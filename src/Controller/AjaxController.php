<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2019 Leo Feyer
 * @package BUF (Beurteilen und Fördern)
 * @author Marko Cupic m.cupic@gmx.ch, 2014-2019
 * @link https://github.com/markocupic/buf-bundle
 * @license MIT
 */

namespace Markocupic\BufBundle\Controller;

use Contao\CommentModel;
use Contao\Database;
use Contao\Date;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\Input;
use Contao\StudentModel;
use Contao\System;
use Contao\TeacherModel;
use Contao\Validator;
use Contao\VotingModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AjaxController
 * @package Markocupic\BufBundle\Controller
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class AjaxController extends AbstractController
{

    /**
     * Handles ajax requests.
     * @Route("/_ajax", name="buf_bundle_ajax_frontend", defaults={"_scope" = "frontend", "_token_check" = true})
     */
    public function ajaxAction()
    {
        $this->container->get('contao.framework')->initialize();
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');

        if (!FE_USER_LOGGED_IN)
        {
            return false;
        }

        if (!Environment::get('isAjaxRequest'))
        {
            return false;
        }

        // Update classlist
        if (Input::get('act') == 'update_classlist')
        {
            $arrJSON = array();
            if (Validator::isAlphabetic(Input::post('lastname')) && Validator::isAlphabetic(Input::post('firstname')))
            {
                $objStudent = StudentModel::findByPk(Input::post('id'));
                if ($objStudent !== null)
                {
                    $objStudent->lastname = Input::post('lastname');
                    $objStudent->firstname = Input::post('firstname');
                    $objStudent->gender = Input::post('gender');
                    if (Input::post('dateOfBirth') == '' || false === strtotime(Input::post('dateOfBirth')))
                    {
                        $dateOfBirth = '';
                    }
                    else
                    {
                        $dateOfBirth = strtotime(Input::post('dateOfBirth'));
                    }
                    $objStudent->dateOfBirth = $dateOfBirth;
                    $objStudent->tstamp = time();
                    $objStudent->save();
                    $arrJSON['status'] = 'success';
                }
            }
            else
            {
                $arrJSON['status'] = 'error';
                $arrJSON['message'] = 'Ungültige Zeichenkette!';
            }

            return new JsonResponse($arrJSON);
        }

        // Delete a student
        if (Input::get('act') == 'delete_student')
        {
            $arrJSON = array();
            $arrJSON['status'] = 'error';
            if (TeacherModel::getOwnClass())
            {
                // Delete student
                $objDb = Database::getInstance()->prepare('DELETE FROM tl_student WHERE id=?')->execute(Input::post('id'));
                if ($objDb->affectedRows)
                {
                    $arrJSON['status'] = 'success';
                }
                // Delete referenced votings
                Database::getInstance()->prepare('DELETE FROM tl_voting WHERE student=?')->execute(Input::post('id'));
            }
            return new JsonResponse($arrJSON);
        }

        // Toggle visibility of a student
        if (Input::get('act') == 'toggle_student')
        {
            $arrJSON = array();
            $arrJSON['status'] = 'error';
            if (TeacherModel::getOwnClass())
            {
                $objStudent = StudentModel::findByPk(Input::post('id'));
                if ($objStudent !== null)
                {
                    if ($objStudent->disable == '1')
                    {
                        $objStudent->disable = '';
                    }
                    else
                    {
                        $objStudent->disable = '1';
                    }
                    $objStudent->save();
                    $arrJSON['status'] = 'success';
                    $arrJSON['disable'] = $objStudent->disable;
                }
            }
            return new JsonResponse($arrJSON);
        }

        // Reset the voting table
        if (Input::get('act') == 'reset_table')
        {
            $arrTable = VotingModel::getRows(Input::get('class'), Input::get('subject'), Input::get('teacher'));
            $arrJSON = array('status' => 'success', 'rows' => $arrTable['Datensaetze']);
            return new JsonResponse($arrJSON);
        }

        // Update voting table
        if (Input::get('act') == 'update')
        {
            $rating = VotingModel::update(Input::post('student'), Input::post('teacher'), Input::post('subject'), Input::post('skill'), Input::post('value'));
            if ($rating)
            {
                $arrJSON = array('status' => 'success', 'rating' => $rating, 'message' => 'Submitted successfully.');
            }
            else
            {
                $arrJSON = array('status' => 'success', 'rating' => '', 'message' => 'Invalid value submitted: ' . Input::post('value'));
            }
            return new JsonResponse($arrJSON);
        }

        // Get comment modal
        if (Input::get('act') == 'get_comment_modal')
        {
            $strModal = CommentModel::getCommentModal(Input::post('student'), Input::post('teacher'), Input::post('subject'));
            $arrJSON = array('status' => 'success', 'strModal' => $strModal);
            return new JsonResponse($arrJSON);
        }

        // New comment
        if (Input::get('act') == 'new_comment')
        {
            $loggedInFeUser = FrontendUser::getInstance();
            $objComment = new CommentModel();
            $objComment->published = true;
            $objComment->dateOfCreation = time();
            $objComment->subject = Input::post('subject');
            $objComment->student = Input::post('student');
            $objComment->teacher = $loggedInFeUser->id;
            $objComment->save();
            System::log('A new entry "tl_content.id=' . $objComment->id . '" has been created', __METHOD__, TL_GENERAL);

            $arrJSON = array();
            $arrJSON['status'] = 'success';

            $objRows = Database::getInstance()->prepare('SELECT * FROM tl_comment WHERE teacher=? AND subject=? AND student=? ORDER BY dateOfCreation DESC, tstamp DESC')
                ->execute($objComment->teacher, $objComment->subject, $objComment->student);

            $tableRows = '';
            while ($objRows->next())
            {
                $objPartial = new FrontendTemplate('voting_comment_modal_row');
                $objPartial->id = $objRows->id;
                $objPartial->published = $objRows->published;
                $objPartial->subject = $objRows->subject;
                $objPartial->student = $objRows->student;
                $objPartial->dateOfCreation = Date::parse('Y-m-d', $objRows->dateOfCreation);
                $objPartial->comment = nl2br($objRows->comment);
                $tableRows .= $objPartial->parse();
            }
            $arrJSON['tableRows'] = $tableRows;
            return new JsonResponse($arrJSON);
        }

        // Delete comment
        if (Input::get('act') == 'delete_comment')
        {
            $loggedInFeUser = FrontendUser::getInstance();
            $objComment = CommentModel::findByPk(Input::post('id'));
            if ($objComment !== null)
            {
                if ($objComment->teacher == $loggedInFeUser->id)
                {
                    $objComment->delete();
                    System::log('DELETE FROM tl_comment WHERE id=' . Input::post('id'), __METHOD__, TL_GENERAL);
                    $arrJSON = array();
                    $arrJSON['status'] = 'success';
                    return new JsonResponse($arrJSON);
                }
            }
        }

        // Toggle visibility
        if (Input::get('act') == 'toggle_visibility')
        {
            $loggedInFeUser = FrontendUser::getInstance();
            $objComment = CommentModel::findByPk(Input::post('id'));
            if ($objComment !== null)
            {
                if ($objComment->teacher == $loggedInFeUser->id)
                {
                    if ($objComment->published > 0)
                    {
                        $objComment->published = 0;
                    }
                    else
                    {
                        $objComment->published = 1;
                    }
                    $objComment->save();
                    $arrJSON = array();
                    $arrJSON['status'] = 'success';
                    $arrJSON['published'] = $objComment->published;

                    return new JsonResponse($arrJSON);
                }
            }
        }

        // Get comment
        if (Input::get('act') == 'get_comment')
        {
            $objComment = CommentModel::findByPk(Input::post('id'));
            if ($objComment !== null)
            {
                $arrJSON = $objComment->row();
                $arrJSON['comment'] = html_entity_decode($arrJSON['comment']);
                $arrJSON['status'] = 'success';
                $arrJSON['dateOfCreation'] = Date::parse('Y-m-d', $arrJSON['dateOfCreation']);
                return new JsonResponse($arrJSON);
            }
        }

        // Save comment
        if (Input::get('act') == 'save_comment')
        {
            $objComment = CommentModel::findByPk(Input::post('id'));
            if ($objComment !== null)
            {
                $loggedInFeUser = FrontendUser::getInstance();
                if ($loggedInFeUser->id == $objComment->teacher)
                {
                    $strDate = trim(Input::post('dateOfCreation'));
                    $strDate = $strDate == '' ? Date::parse('Y-m-d') : $strDate;
                    $objDate = new Date($strDate, 'Y-m-d');

                    if (trim(Input::post('dateOfCreation')) != '')
                    {
                        if ($objDate->dateOfCreation != $objComment->dateOfCreation)
                        {
                            $objComment->dateOfCreation = $objDate->tstamp;
                            $objComment->tstamp = time();
                            $objComment->adviced = '';
                        }
                        System::log('A new version of record "tl_comment.id=' . $objComment->id . '" has been created', __METHOD__, TL_GENERAL);
                    }

                    if (trim(Input::post('comment')) != '')
                    {
                        if (trim(Input::post('comment')) != $objComment->comment)
                        {
                            $objComment->comment = trim(Input::post('comment'));
                            $objComment->tstamp = time();
                            $objComment->adviced = '';
                        }
                        System::log('A new version of record "tl_comment.id=' . $objComment->id . '" has been created', __METHOD__, TL_GENERAL);
                    }

                    $objComment->save();

                    // Delete, when empty
                    if (trim($objComment->comment) == '')
                    {
                        $objComment->delete();
                    }

                    $arrJSON = array();
                    $arrJSON['status'] = 'success';

                    $objRows = Database::getInstance()->prepare('SELECT * FROM tl_comment WHERE teacher=? AND subject=? AND student=? ORDER BY dateOfCreation DESC, tstamp DESC')
                        ->execute($objComment->teacher, $objComment->subject, $objComment->student);

                    $tableRows = '';
                    while ($objRows->next())
                    {
                        $objPartial = new FrontendTemplate('voting_comment_modal_row');
                        $objPartial->id = $objRows->id;
                        $objPartial->published = $objRows->published;
                        $objPartial->subject = $objRows->subject;
                        $objPartial->student = $objRows->student;
                        $objPartial->dateOfCreation = Date::parse('Y-m-d', $objRows->dateOfCreation);
                        $objPartial->comment = nl2br(html_entity_decode($objRows->comment));
                        $tableRows .= $objPartial->parse();
                    }
                    $arrJSON['tableRows'] = $tableRows;
                    return new JsonResponse($arrJSON);
                }
            }
        }

        // Update teacher's deviation tolerance
        if (Input::get('act') == 'updateTeachersDeviationTolerance')
        {
            $arrJSON = array('status' => 'error', 'deviation' => '');
            if (is_numeric(Input::post('tolerance')) && Input::post('tolerance') > 0 && Input::post('tolerance') < 3.1)
            {
                $loggedInFeUser = FrontendUser::getInstance();
                if ($loggedInFeUser !== null)
                {
                    $objTeacher = TeacherModel::findByPk($loggedInFeUser->id);
                    if ($objTeacher !== null)
                    {
                        $objTeacher->deviation = Input::post('tolerance');
                        $objTeacher->save();
                        $arrJSON['status'] = 'success';
                        $arrJSON['deviation'] = Input::post('tolerance');
                    }
                }
            }
            return new JsonResponse($arrJSON);
        }

        // Update teacher's showCommentsNotOlderThen
        if (Input::get('act') == 'updateTeachersShowCommentsTimeRange')
        {
            $timeRange = Input::post('timeRange');
            $teacherId = Input::post('teacherId');
            $arrJSON = array('status' => 'error', 'timeRange' => '');

            if (is_numeric($timeRange) && $timeRange < 37)
            {
                $objTeacher = TeacherModel::findByPk($teacherId);
                if ($objTeacher !== null)
                {
                    $objTeacher->showCommentsNotOlderThen = $timeRange;
                    $objTeacher->save();
                    $arrJSON['status'] = 'success';
                    $arrJSON['timeRange'] = $timeRange;
                }
            }

            return new JsonResponse($arrJSON);
        }

        // Delete all votings in a column or in a row
        if (Input::get('act') == 'delete_row_or_col')
        {
            $mode = Input::post('mode');
            $colOrRow = Input::post('colOrRow');
            if (VotingModel::deleteRowOrCol($mode, $colOrRow, Input::post('teacher'), Input::post('subject'), Input::post('class')))
            {
                $arrJSON = array('status' => 'deleted', 'intIndex' => $colOrRow);
            }
            else
            {
                $arrJSON = array('status' => 'error', 'intIndex' => $colOrRow);
            }

            return new JsonResponse($arrJSON);
        }

        // Appear the info Box in the tally sheet mode
        if (Input::get('act') == 'tally_sheet')
        {
            if (VotingModel::getInfoBox(Input::post('studentId'), Input::post('skillId')))
            {
                $arrJSON = array('status' => 'success', 'html' => VotingModel::getInfoBox(Input::post('studentId'), Input::post('skillId')));
            }
            else
            {
                $arrJSON = array('status' => 'error', 'html' => '');
            }
            return new JsonResponse($arrJSON);
        }
    }
}