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
namespace Markocupic\Buf;


/**
 * Class AccountSettingsController
 * Front end module buf
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class AccountSettingsController extends \Frontend
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
        $submitted = false;
        $hasErrors = false;
        $set = array();
        $this->import('FrontendUser', 'User');
        $objTeacher = \TeacherModel::findByPk($this->User->id);

        if ($_SESSION['submitted']) {
            unset($_SESSION['submitted']);
            $objTemplate->submitted = true;
        }




        /** EmailField **/
        $widget = new \TextField();
        $widget->value = $objTeacher->email;
        $widget->id = 'email';
        $widget->name = 'email';
        $widget->label = 'E-Mail-Adresse';
        $widget->placeholder = 'vorname.nachname@ettiswil.educanet2.ch';
        $widget->mandatory = true;
        $widget->rgxp = 'email';
        if ($_POST && \Input::post('FORM_SUBMIT') == 'tl_member_account_settings') {
            $widget->validate();
            if (!$widget->hasErrors()) {
                $set['email'] = $widget->value;
                $submitted = true;
            } else {
                $hasErrors = true;
            }
        }
        $objTemplate->emailLabel = $widget->generateLabel();
        $objTemplate->email = $widget->generateWithError(true);


        /** PasswordField **/
        $widget = new \Password();
        $widget->id = 'password';
        $widget->name = 'password';
        $widget->label = 'Passwort';
        $widget->placeholder = '********';
        if ($_POST && \Input::post('FORM_SUBMIT') == 'tl_member_password_settings') {
            $widget->validate();
            if (!$widget->hasErrors()) {
                $set['password'] = $widget->value;
                $submitted = true;
            } else {
                $hasErrors = true;
            }
        }
        $objTemplate->passwordLabel = $widget->generateLabel();
        $objTemplate->password = $widget->generateWithError(true);
        $objTemplate->confirmationLabel = $widget->generateLabel();
        $objTemplate->confirmation = $widget->generateConfirmation();

        /** adviceOnNewComments **/
        if ($objTeacher->adviceOnNewComments) {
            $objTemplate->adviceOnNewCommentsChecked = ' checked';
        }

        if ($_POST && \Input::post('FORM_SUBMIT') == 'tl_member_account_settings') {
            $set['adviceOnNewComments'] = \Input::post('adviceOnNewComments');
            $submitted = true;
        }


        if($_POST)
        {
            $_SESSION['FORM_SUBMIT'] = \Input::post('FORM_SUBMIT');
        }

        // Reload page
        if ($submitted && !$hasErrors && count($set)) {
            \Database::getInstance()->prepare('UPDATE tl_member %s WHERE id=?')->set($set)->execute($objTeacher->id);
            $_SESSION['submitted'] = true;
            $this->reload();
        }

        $objTemplate->tl_form_submit = $_SESSION['FORM_SUBMIT'] ? $_SESSION['FORM_SUBMIT'] : 'tl_account_settings';
        unset($_SESSION['FORM_SUBMIT']);


        $widget->value = $objTeacher->adviceOnNewComments;

        $objTemplate->adviceOnNewCommentsLabel = $widget->generateLabel();
        $objTemplate->adviceOnNewComments = $widget->parse();


        // other properties
        $objTemplate->username = $objTeacher->username;
        $objTemplate->gender = $objTeacher->gender;

        $objTemplate->UserFullname = \TeacherModel::getFullName($objTeacher->id);
        $objTemplate->function = \TeacherModel::isClassTeacher() ? 'Klassenlehrer von ' . \ClassModel::getName(\TeacherModel::isClassTeacher()) : 'FachlehrerIn';

        $objTemplate->backLink = $this->generateFrontendUrl($objPage->row(), '/do/dashboard');
        $objTemplate->method = 'post';
        $objTemplate->formId1 = 'tl_member_account_settings';
        $objTemplate->formId2 = 'tl_member_password_settings';
        $objTemplate->slabel1 = specialchars($GLOBALS['TL_LANG']['MSC']['saveData']);
        $objTemplate->slabel2 = 'Passwort speichern';

        $objTemplate->action = $this->generateFrontendUrl($objPage->row(), '/do/account_settings') . Helper::setQueryString(array('act' => 'set_password'));
        $objTemplate->enctype = 'application/x-www-form-urlencoded';

        return $objTemplate;
    }
}