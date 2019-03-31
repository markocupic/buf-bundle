<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2014 Leo Feyer
 * @package BUF (Beurteilen und Fördern)
 * @author Marko Cupic m.cupic@gmx.ch, 2014
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */

namespace Markocupic\BufBundle;

/**
 * Class ModuleLogin
 * Front end module "login".
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class MainController extends \Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'dashboard';

    public function generate()
    {
        global $objPage;

        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### Beurteilen und Fördern ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        if (TL_MODE == 'FE')
        {
            // Load languages
            \System::loadLanguageFile('tl_class');
            \System::loadLanguageFile('tl_member');
            \System::loadLanguageFile('tl_settings');
            \System::loadLanguageFile('tl_student');
            \System::loadLanguageFile('tl_subject');
            \System::loadLanguageFile('tl_voting');

            if (FE_USER_LOGGED_IN && \Input::get('isAjax') == 'true')
            {
                $this->generateAjax();
                exit;
            }
            elseif (!FE_USER_LOGGED_IN && \Input::get('do') != 'login')
            {
                \Input::resetCache();
                $url = $this->generateFrontendUrl($objPage->row(), '/do/login');
                $this->redirect($url);
            }
            elseif (!FE_USER_LOGGED_IN && \Input::get('do') == 'login')
            {
                $this->strTemplate = \Input::get('do');
            }
            elseif (FE_USER_LOGGED_IN && !\Input::get('do'))
            {
                \Input::resetCache();
                $url = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
                $this->redirect($url);
            }
            elseif (FE_USER_LOGGED_IN && \Input::get('do') != '')
            {
                if (\Input::get('do') == 'print_table' || \Input::get('do') == 'print_average_table' || \Input::get('do') == 'print_tally_sheet' || \Input::get('do') == 'print_data_sheet' || \Input::get('do') == 'print_data_sheet_doc')
                {
                    $this->strTemplate = null;
                }
                else
                {
                    $this->strTemplate = \Input::get('do');
                }
            }
            else
            {
                // logout and redirect to the login form
                if (FE_USER_LOGGED_IN)
                {
                    $this->import('FrontendUser', 'User');
                    $this->User->logout();
                }
                \Input::resetCache();
                $url = $this->generateFrontendUrl($objPage->row(), '/do/login');
                $this->redirect($url);
            }
        }

        return parent::generate();
    }

    /**
     * Method called on Ajax Requests
     */
    public function generateAjax()
    {
        if (!FE_USER_LOGGED_IN)
        {
            return;
        }
        $this->import('FrontendUser', 'User');

        // edit student
        if (\Input::get('act') == 'update_classlist')
        {
            $arrJSON = array();

            if (\Validator::isAlphabetic(\Input::post('lastname')) && \Validator::isAlphabetic(\Input::post('firstname')))
            {
                $objStudent = \StudentModel::findByPk(\Input::post('id'));
                if ($objStudent !== null)
                {
                    $objStudent->lastname = \Input::post('lastname');
                    $objStudent->firstname = \Input::post('firstname');
                    $objStudent->gender = \Input::post('gender');
                    if (\Input::post('dateOfBirth') == '' || false === strtotime(\Input::post('dateOfBirth')))
                    {
                        $dateOfBirth = '';
                    }
                    else
                    {
                        $dateOfBirth = strtotime(\Input::post('dateOfBirth'));
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

            die(json_encode($arrJSON));
        }

        // delete a student
        if (\Input::get('act') == 'delete_student')
        {
            $arrJSON = array();
            $arrJSON['status'] = 'error';
            if (\TeacherModel::getOwnClass())
            {
                // delete student
                $objDb = \Database::getInstance()->prepare('DELETE FROM tl_student WHERE id=?')->execute(\Input::post('id'));
                if ($objDb->affectedRows)
                {
                    $arrJSON['status'] = 'success';
                }
                // delete referenced votings
                \Database::getInstance()->prepare('DELETE FROM tl_voting WHERE student=?')->execute(\Input::post('id'));
            }
            die(json_encode($arrJSON));
        }

        // toggle visibility of a student
        if (\Input::get('act') == 'toggle_student')
        {
            $arrJSON = array();
            $arrJSON['status'] = 'error';
            if (\TeacherModel::getOwnClass())
            {
                $objStudent = \StudentModel::findByPk(\Input::post('id'));
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
            die(json_encode($arrJSON));
        }

        // reset the voting table
        if (\Input::get('act') == 'reset_table')
        {
            $arrTable = \VotingModel::getRows(\Input::get('class'), \Input::get('subject'), \Input::get('teacher'));
            $arrJSON = array('status' => 'success', 'rows' => $arrTable['Datensaetze']);
            die(json_encode($arrJSON));
        }

        // update voting table
        if (\Input::get('act') == 'update')
        {
            $rating = \VotingModel::update(\Input::post('student'), \Input::post('teacher'), \Input::post('subject'), \Input::post('skill'), \Input::post('value'));
            if ($rating)
            {
                $arrJSON = array('status' => 'success', 'rating' => $rating, 'message' => 'Submitted successfully.');
            }
            else
            {
                $arrJSON = array('status' => 'success', 'rating' => '', 'message' => 'Invalid value submitted: ' . \Input::post('value'));
            }
            die(json_encode($arrJSON));
        }

        // update voting table
        if (\Input::get('act') == 'get_comment_modal')
        {
            $strModal = \CommentModel::getCommentModal(\Input::post('student'), \Input::post('teacher'), \Input::post('subject'));
            $arrJSON = array('status' => 'success', 'strModal' => $strModal);
            die(json_encode($arrJSON));
        }

        // new comment
        if (\Input::get('act') == 'new_comment')
        {
            $objUser = \FrontendUser::getInstance();
            $objComment = new \CommentModel();
            $objComment->published = true;
            $objComment->dateOfCreation = time();
            $objComment->subject = \Input::post('subject');
            $objComment->student = \Input::post('student');
            $objComment->teacher = $objUser->id;
            $objComment->save();
            \System::log('A new entry "tl_content.id=' . $objComment->id . '" has been created', __METHOD__, TL_GENERAL);

            $arrJSON = array();
            $arrJSON['status'] = 'success';

            $objRows = \Database::getInstance()->prepare('SELECT * FROM tl_comment WHERE teacher=? AND subject=? AND student=? ORDER BY dateOfCreation DESC, tstamp DESC')
                ->execute($objComment->teacher, $objComment->subject, $objComment->student);

            $tableRows = '';
            while ($objRows->next())
            {
                $objPartial = new \FrontendTemplate('voting_comment_modal_row');
                $objPartial->id = $objRows->id;
                $objPartial->published = $objRows->published;
                $objPartial->subject = $objRows->subject;
                $objPartial->student = $objRows->student;
                $objPartial->dateOfCreation = \Date::parse('Y-m-d', $objRows->dateOfCreation);
                $objPartial->comment = nl2br($objRows->comment);
                $tableRows .= $objPartial->parse();
            }
            $arrJSON['tableRows'] = $tableRows;
            die(json_encode($arrJSON));
        }

        // delete comment
        if (\Input::get('act') == 'delete_comment')
        {
            $objUser = \FrontendUser::getInstance();
            $objComment = \CommentModel::findByPk(\Input::post('id'));
            if ($objComment !== null)
            {
                if ($objComment->teacher == $objUser->id)
                {
                    $objComment->delete();
                    \System::log('DELETE FROM tl_comment WHERE id=' . \Input::post('id'), __METHOD__, TL_GENERAL);
                    $arrJSON = array();
                    $arrJSON['status'] = 'success';
                    die(json_encode($arrJSON));
                }
            }

            exit;
        }

        // toggle visibility
        if (\Input::get('act') == 'toggle_visibility')
        {
            $objUser = \FrontendUser::getInstance();
            $objComment = \CommentModel::findByPk(\Input::post('id'));
            if ($objComment !== null)
            {
                if ($objComment->teacher == $objUser->id)
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

                    die(json_encode($arrJSON));
                }
            }

            exit;
        }

        // get comment
        if (\Input::get('act') == 'get_comment')
        {
            $objComment = \CommentModel::findByPk(\Input::post('id'));
            if ($objComment !== null)
            {
                $arrJSON = $objComment->row();
                $arrJSON['comment'] = html_entity_decode($arrJSON['comment']);
                $arrJSON['status'] = 'success';
                $arrJSON['dateOfCreation'] = \Date::parse('Y-m-d', $arrJSON['dateOfCreation']);
                die(json_encode($arrJSON));
            }
            exit();
        }

        // save comment
        if (\Input::get('act') == 'save_comment')
        {
            $objComment = \CommentModel::findByPk(\Input::post('id'));
            if ($objComment !== null)
            {
                $objUser = \FrontendUser::getInstance();
                if ($objUser->id == $objComment->teacher)
                {
                    $strDate = trim(\Input::post('dateOfCreation'));
                    $strDate = $strDate == '' ? \Date::parse('Y-m-d') : $strDate;
                    $objDate = new \Date($strDate, 'Y-m-d');

                    if (trim(\Input::post('dateOfCreation')) != '')
                    {
                        if ($objDate->dateOfCreation != $objComment->dateOfCreation)
                        {
                            $objComment->dateOfCreation = $objDate->tstamp;
                            $objComment->tstamp = time();
                            $objComment->adviced = '';
                        }
                        \System::log('A new version of record "tl_comment.id=' . $objComment->id . '" has been created', __METHOD__, TL_GENERAL);
                    }

                    if (trim(\Input::post('comment')) != '')
                    {
                        if (trim(\Input::post('comment')) != $objComment->comment)
                        {
                            $objComment->comment = trim(\Input::post('comment'));
                            $objComment->tstamp = time();
                            $objComment->adviced = '';
                        }
                        \System::log('A new version of record "tl_comment.id=' . $objComment->id . '" has been created', __METHOD__, TL_GENERAL);
                    }

                    $objComment->save();

                    // Delete, when empty
                    if (trim($objComment->comment) == '')
                    {
                        $objComment->delete();
                    }

                    $arrJSON = array();
                    $arrJSON['status'] = 'success';

                    $objRows = \Database::getInstance()->prepare('SELECT * FROM tl_comment WHERE teacher=? AND subject=? AND student=? ORDER BY dateOfCreation DESC, tstamp DESC')
                        ->execute($objComment->teacher, $objComment->subject, $objComment->student);

                    $tableRows = '';
                    while ($objRows->next())
                    {
                        $objPartial = new \FrontendTemplate('voting_comment_modal_row');
                        $objPartial->id = $objRows->id;
                        $objPartial->published = $objRows->published;
                        $objPartial->subject = $objRows->subject;
                        $objPartial->student = $objRows->student;
                        $objPartial->dateOfCreation = \Date::parse('Y-m-d', $objRows->dateOfCreation);
                        $objPartial->comment = nl2br(html_entity_decode($objRows->comment));
                        $tableRows .= $objPartial->parse();
                    }
                    $arrJSON['tableRows'] = $tableRows;
                    die(json_encode($arrJSON));
                }
            }
            exit();
        }

        // update teacher's deviation tolerance
        if (\Input::get('act') == 'updateTeachersDeviationTolerance')
        {
            $arrJSON = array('status' => 'error', 'deviation' => '');
            if (is_numeric(\Input::post('tolerance')) && \Input::post('tolerance') > 0 && \Input::post('tolerance') < 3.1)
            {
                $objTeacher = \TeacherModel::findByPk($this->User->id);
                if ($objTeacher !== null)
                {
                    $objTeacher->deviation = \Input::post('tolerance');
                    $objTeacher->save();
                    $arrJSON['status'] = 'success';
                    $arrJSON['deviation'] = \Input::post('tolerance');
                }
            }
            die(json_encode($arrJSON));
        }

        // update teacher's showCommentsNotOlderThen
        if (\Input::get('act') == 'updateTeachersShowCommentsTimeRange')
        {
            $timeRange = \Input::post('timeRange');
            $teacherId = \Input::post('teacherId');
            $arrJSON = array('status' => 'error', 'timeRange' => '');

            if (is_numeric($timeRange) && $timeRange < 37)
            {
                $objTeacher = \TeacherModel::findByPk($teacherId);
                if ($objTeacher !== null)
                {
                    $objTeacher->showCommentsNotOlderThen = $timeRange;
                    $objTeacher->save();
                    $arrJSON['status'] = 'success';
                    $arrJSON['timeRange'] = $timeRange;
                }
            }

            die(json_encode($arrJSON));
        }

        // delete all votings in a column or in a row
        if (\Input::get('act') == 'delete_row_or_col')
        {
            $mode = \Input::post('mode');
            $colOrRow = \Input::post('colOrRow');
            if (\VotingModel::deleteRowOrCol($mode, $colOrRow, \Input::post('teacher'), \Input::post('subject'), \Input::post('class')))
            {
                $arrJSON = array('status' => 'deleted', 'intIndex' => $colOrRow);
            }
            else
            {
                $arrJSON = array('status' => 'error', 'intIndex' => $colOrRow);
            }

            die(json_encode($arrJSON));
        }

        // appear the info Box in the tally sheet mode
        if (\Input::get('act') == 'tally_sheet')
        {
            if (\VotingModel::getInfoBox(\Input::post('studentId'), \Input::post('skillId')))
            {
                $arrJSON = array('status' => 'success', 'html' => \VotingModel::getInfoBox(\Input::post('studentId'), \Input::post('skillId')));
            }
            else
            {
                $arrJSON = array('status' => 'error', 'html' => '');
            }
            die(json_encode($arrJSON));
        }
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        global $objPage;
        $this->import('FrontendUser', 'User');

        // decode, if query string is encoded
        if (\Input::get('vars') && $GLOBALS['TL_CONFIG']['buf_encode_params'])
        {
            $plaintext_dec = Cipher::decrypt(\Input::get('vars'));
            $arrGet = explode('&', $plaintext_dec);
            foreach ($arrGet as $chunk)
            {
                $arrItem = explode('=', $chunk);
                \Input::setGet($arrItem[0], $arrItem[1]);
            }
        }

        // switch
        switch (\Input::get('do'))
        {
            case 'login':
                $objController = new LoginController($this);
                $objController->authenticate();
                $this->Template = $objController->setTemplate($this->Template);
                break;

            case 'dashboard':
                $objController = new DashboardController($this);
                $this->Template = $objController->setTemplate($this->Template);
                break;

            case 'start_new_voting':
                $objController = new StartNewVotingController($this);
                $this->Template = $objController->setTemplate($this->Template);
                break;

            case 'voting_table':
                $blnError = false;
                if (!is_numeric(\Input::get('teacher')) || !is_numeric(\Input::get('class')) || !is_numeric(\Input::get('subject')))
                {
                    $blnError = true;
                }
                if (\Input::get('teacher') != $this->User->id && \Input::get('class') != $this->User->class)
                {
                    $blnError = true;
                }
                if ($blnError)
                {
                    $url = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
                    $this->redirect($url);
                }

                $objController = new VotingTableController($this);
                $this->Template = $objController->setTemplate($this->Template);
                break;

            case 'print_table':
                if (\TeacherModel::getOwnClass() != \Input::get('class') && \Input::get('teacher') != $this->User->id)
                {
                    $url = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
                    $this->redirect($url);
                }
                $objController = new FpdfController($this);
                $objController->printTable();
                break;

            case 'print_average_table':
                if (!\TeacherModel::getOwnClass())
                {
                    $url = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
                    $this->redirect($url);
                }
                //$objController = new FpdfController($this);
                //$objController->printAverageTable();
                $objController = new PHPWordController($this);
                $objController->printAverageTable();
                break;

            case 'print_tally_sheet':
                if (!\TeacherModel::getOwnClass())
                {
                    $url = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
                    $this->redirect($url);
                }
                $objController = new FpdfController($this);
                $objController->printTallySheet();
                break;

            // abandoned since 14.01.2017 !!!!!
            case 'print_data_sheet_pdf':
                if (!\TeacherModel::getOwnClass())
                {
                    $url = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
                    $this->redirect($url);
                }
                $objController = new TcpdfController($this);
                $objController->printDataSheet();
                break;

            case 'print_data_sheet':
                if (!\TeacherModel::getOwnClass())
                {
                    $url = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
                    $this->redirect($url);
                }
                $objController = new PHPWordController($this);
                $objController->printDataSheet();
                break;

            case 'delete_skills_only':
                if (\Input::get('teacher') == $this->User->id || \TeacherModel::getOwnClass() == \Input::get('class'))
                {
                    $this->import('Database');
                    // Delete votings
                    $this->Database->prepare('DELETE FROM tl_voting WHERE teacher=? AND subject=? AND student IN (SELECT id FROM tl_student WHERE class=?)')->execute((int)\Input::get('teacher'), (int)\Input::get('subject'), (int)\Input::get('class'));
                }
                $url = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
                $this->redirect($url);
                break;

            case 'delete_comments_only':
                if (\Input::get('teacher') == $this->User->id || \TeacherModel::getOwnClass() == \Input::get('class'))
                {
                    $this->import('Database');
                    // Delete comments
                    $this->Database->prepare('DELETE FROM tl_comment WHERE teacher=? AND subject=? AND student IN (SELECT id FROM tl_student WHERE class=?)')->execute((int)\Input::get('teacher'), (int)\Input::get('subject'), (int)\Input::get('class'));
                }
                $url = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
                $this->redirect($url);
                break;

            case 'account_settings':
                $objController = new AccountSettingsController($this);
                $this->Template = $objController->setTemplate($this->Template);
                break;

            case 'edit_classlist':
                if (!\TeacherModel::getOwnClass())
                {
                    $url = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
                    $this->redirect($url);
                }
                $objController = new EditClasslistController($this);
                $this->Template = $objController->setTemplate($this->Template);
                break;

            case 'average_table':
                if (!\TeacherModel::getOwnClass())
                {
                    $url = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
                    $this->redirect($url);
                }
                $objController = new AverageTableController($this);
                $this->Template = $objController->setTemplate($this->Template);
                break;

            case 'tally_sheet':
                if (!\TeacherModel::getOwnClass())
                {
                    $url = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
                    $this->redirect($url);
                }
                $objController = new TallySheetController($this);
                $this->Template = $objController->setTemplate($this->Template);
                break;
        }
    }
}
