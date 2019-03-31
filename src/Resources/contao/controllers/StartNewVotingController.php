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
 * Class StartNewVotingController
 * @package Markocupic\BufBundle
 */
class StartNewVotingController extends \Frontend
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

        // redirect to the voting table if inputs are valid
        if (\Input::post('TL_FORM') == 'start_new_voting') {
            if (\SubjectModel::findByPk(\Input::post('subject')) && \ClassModel::findByPk(\Input::post('class')))
            {
                $url = $this->generateFrontendUrl($objPage->row(), '/do/voting_table');
                $arrQuery = array('teacher' => $this->User->id, 'subject' => \Input::post('subject'), 'class' => \Input::post('class'));
                $url .= Helper::setQueryString($arrQuery);
                $this->redirect($url);
            }
        }
        // show form
        // get option tags for classes
        $opt = '<option value="0">leer</option>';
        $objClass = \ClassModel::findAll(array('order' => 'name ASC'));
        if ($objClass !== null) {
            while ($objClass->next()) {
                $opt .= sprintf('<option value="%s">%s</option>', $objClass->id, $objClass->name);
            }
        }
        $objTemplate->classes = $opt;

        // get option tags for subjects
        $opt = '<option value="0">leer</option>';
        $objSubject = \SubjectModel::findAll(array('order' => 'name ASC'));
        if ($objSubject !== null) {
            while ($objSubject->next()) {
                $opt .= sprintf('<option value="%s">%s</option>', $objSubject->id, $objSubject->name);
            }
        }
        $objTemplate->subjects = $opt;

        return $objTemplate;
    }
}
