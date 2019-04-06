<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2019 Leo Feyer
 * @package BUF (Beurteilen und Fördern)
 * @author Marko Cupic m.cupic@gmx.ch, 2014-2019
 * @link https://github.com/markocupic/buf-bundle
 * @license MIT
 */

namespace Markocupic\BufBundle;

/**
 * Class DashboardController
 * @package Markocupic\BufBundle
 */
class DashboardController extends \Frontend
{

    /**
     * @var $objMainController
     */
    protected $objMainController;

    public function __construct($objMainController)
    {
        $this->objMainController = $objMainController;
        $this->import('FrontendUser', 'User');
        return parent::__construct();
    }

    /**
     * Generate the module
     */
    public function setTemplate($objTemplate)
    {
        global $objPage;
        $objTemplate->User = $this->User;
        $objTemplate->hrefNewVoting = $this->generateFrontendUrl($objPage->row(), '/do/start_new_voting');
        $objTemplate->hrefVotingTable = $this->generateFrontendUrl($objPage->row(), '/do/voting_table');

        // get all votings of the current user
        $user_id = $this->User->id;
        $arrVotings = [];
        $sql = 'SELECT tl_voting.teacher AS teacherId, tl_voting.subject AS subjectId, tl_class.id AS classId, tl_class.name AS classname, tl_subject.name AS subjectname
				FROM tl_voting, tl_class, tl_subject
				WHERE tl_voting.teacher = ? AND (SELECT class FROM tl_student WHERE id = tl_voting.student) = tl_class.id AND tl_subject.id = tl_voting.subject
				GROUP BY tl_voting.teacher, (SELECT class FROM tl_student WHERE tl_student.disable=? AND id = tl_voting.student), tl_voting.subject
				ORDER BY tl_class.name, tl_class.id, tl_subject.name, tl_subject.id';
        $objDb = \Database::getInstance()->prepare($sql)->execute($this->User->id, '');
        while ($objDb->next())
        {
            $lastChange = \VotingModel::getLastChange($objDb->teacherId, $objDb->subjectId, $objDb->classId);
            $age = round((time() - $lastChange) / 86400, 0);
            $arrVotings[$objDb->teacherId . '-' . $objDb->subjectId . '-' . $objDb->classId] = array(
                'teacherFullName' => \TeacherModel::getFullName($objDb->teacherId),
                'teacherId'       => $objDb->teacherId,
                'subjectId'       => $objDb->subjectId,
                'subjectName'     => \SubjectModel::getName($objDb->subjectId),
                'classId'         => $objDb->classId,
                'className'       => \ClassModel::getName($objDb->classId),
                'lastChange'      => $lastChange,
                'age'             => $age == 0 ? 'heute' : sprintf('vor %s d', $age),
                'intComments'     => \CommentModel::countCommentsFromVotingTable($objDb->classId, $objDb->teacherId, $objDb->subjectId)
            );
        }

        $objClass = \Database::getInstance()->query('SELECT * FROM tl_class');
        while ($objClass->next())
        {
            // Get all Comments Grouped by subjectId-teacherId
            $objComments = \Database::getInstance()->prepare("SELECT DISTINCT CONCAT(tl_comment.subject,'-',tl_comment.teacher) AS teststring FROM tl_comment WHERE tl_comment.student IN (SELECT id FROM tl_student WHERE tl_student.class=? AND tl_student.disable=?) AND tl_comment.teacher=?")->execute($objClass->id, '', $this->User->id);
            while ($objComments->next())
            {
                $arrTestString = explode('-', $objComments->teststring);
                $subjectId = $arrTestString[0];
                $teacherId = $arrTestString[1];
                $classId = $objClass->id;

                if (!$arrVotings[$teacherId . '-' . $subjectId . '-' . $classId])
                {
                    $lastChange = \CommentModel::getLastChange($teacherId, $subjectId, $classId);
                    $age = round((time() - $lastChange) / 86400, 0);
                    $arrVotings[$teacherId . '-' . $subjectId . '-' . $classId] = array(
                        'teacherFullName' => \TeacherModel::getFullName($teacherId),
                        'teacherId'       => $teacherId,
                        'subjectId'       => $subjectId,
                        'subjectName'     => \SubjectModel::getName($subjectId),
                        'classId'         => $classId,
                        'className'       => \ClassModel::getName($classId),
                        'lastChange'      => $lastChange,
                        'age'             => $age == 0 ? 'heute' : sprintf('vor %s d', $age),
                        'intComments'     => \CommentModel::countCommentsFromVotingTable($classId, $teacherId, $subjectId)
                    );
                }
            }
        }

        usort($arrVotings, function ($a, $b) {
            return strcmp($a['className'], $b['className']);
        });
        $objTemplate->myVotings = $arrVotings;

        $arrVotings = [];
        // get all votings of the current class
        if (\TeacherModel::getOwnClass())
        {
            $classTeacher = \TeacherModel::getOwnClass();
            $sql = 'SELECT tl_voting.teacher AS teacherId, tl_voting.subject AS subjectId, (SELECT class FROM tl_student WHERE id=tl_voting.student) AS classId
                FROM tl_voting, tl_class, tl_subject
                WHERE (SELECT class FROM tl_student WHERE tl_student.disable=? AND id=tl_voting.student) = ?
                GROUP BY  tl_voting.teacher, tl_voting.subject
                ORDER BY (SELECT name FROM tl_subject WHERE id=tl_voting.subject) ASC';
            $objDb = \Database::getInstance()->prepare($sql)->execute('', $classTeacher);
            while ($objDb->next())
            {
                $lastChangeVoting = \VotingModel::getLastChange($objDb->teacherId, $objDb->subjectId, $objDb->classId);
                $lastChangeComment = \CommentModel::getLastChange($objDb->teacherId, $objDb->subjectId, $objDb->classId);
                $lastChange = $lastChangeComment > $lastChangeVoting ? $lastChangeComment : $lastChangeVoting;
                $age = round((time() - $lastChange) / 86400, 0);
                $arrVotings[$objDb->teacherId . '-' . $objDb->subjectId . '-' . $objDb->classId] = array(
                    'teacherFullName' => \TeacherModel::getFullName($objDb->teacherId),
                    'teacherId'       => $objDb->teacherId,
                    'subjectId'       => $objDb->subjectId,
                    'subjectName'     => \SubjectModel::getName($objDb->subjectId),
                    'classId'         => $objDb->classId,
                    'className'       => \ClassModel::getName($objDb->classId),
                    'lastChange'      => $lastChange,
                    'age'             => $age == 0 ? 'heute' : sprintf('vor %s d', $age),
                    'intComments'     => \CommentModel::countCommentsFromVotingTable($objDb->classId, $objDb->teacherId, $objDb->subjectId)
                );
            }
            // Get all Comments Grouped by subjectId-teacherId
            $objComments = \Database::getInstance()->prepare("SELECT DISTINCT CONCAT(tl_comment.subject,'-',tl_comment.teacher) AS teststring FROM tl_comment WHERE tl_comment.student IN (SELECT id FROM tl_student WHERE tl_student.disable=? AND tl_student.class=?)")->execute('', $classTeacher);
            while ($objComments->next())
            {
                $arrTestString = explode('-', $objComments->teststring);
                $teacherId = $arrTestString[1];
                $subjectId = $arrTestString[0];
                $classId = $classTeacher;
                if (!$arrVotings[$teacherId . '-' . $subjectId . '-' . $classId])
                {
                    $lastChange = \CommentModel::getLastChange($teacherId, $subjectId, $classId);
                    $age = round((time() - $lastChange) / 86400, 0);
                    $arrVotings[$teacherId . '-' . $subjectId . '-' . $classId] = array(
                        'teacherFullName' => \TeacherModel::getFullName($teacherId),
                        'teacherId'       => $teacherId,
                        'subjectId'       => $subjectId,
                        'subjectName'     => \SubjectModel::getName($subjectId),
                        'classId'         => $classId,
                        'className'       => \ClassModel::getName($classId),
                        'lastChange'      => $lastChange,
                        'age'             => $age == 0 ? 'heute' : sprintf('vor %s d', $age),
                        'intComments'     => \CommentModel::countCommentsFromVotingTable($classId, $teacherId, $subjectId)
                    );
                }
            }
            usort($arrVotings, function ($a, $b) {
                return strcmp($a['teacherFullName'], $b['teacherFullName']);
            });
            $objTemplate->votingsOnMyClass = $arrVotings;
        }

        //account settings link
        $url = $this->generateFrontendUrl($objPage->row(), '/do/account_settings');
        $objTemplate->setPasswordLink = $url;

        //edit classlist link
        $url = $this->generateFrontendUrl($objPage->row(), '/do/edit_classlist');
        $objTemplate->editClasslistLink = $url;

        //average Table link
        $url = $this->generateFrontendUrl($objPage->row(), '/do/average_table');
        $objTemplate->averageTableLink = $url;

        //tally sheet link
        $url = $this->generateFrontendUrl($objPage->row(), '/do/tally_sheet');
        $objTemplate->tallySheetLink = $url;

        return $objTemplate;
    }

}
