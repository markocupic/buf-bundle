<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2019 Leo Feyer
 * @package BUF (Beurteilen und Fördern)
 * @author Marko Cupic m.cupic@gmx.ch, 2014-2019
 * @link    https://github.com/markocupic/buf-bundle
 * @license MIT
 */

namespace Markocupic\BufBundle;

/**
 * Class MainController
 * @package Markocupic\BufBundle
 */
class MainController extends \Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'dashboard';

    /**
     * @return string
     */
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

            if (!FE_USER_LOGGED_IN && \Input::get('do') != 'login')
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
                $objController = new PhpWordController($this);
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
                $objController = new PhpWordController($this);
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
