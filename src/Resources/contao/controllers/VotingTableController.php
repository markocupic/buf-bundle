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
 * Class VotingTableController
 * Front end module buf
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class VotingTableController extends \Frontend
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

        // delete table href
        $arrQuery = array('teacher' => \Input::get('teacher'), 'subject' => \Input::get('subject'), 'class' => \Input::get('class'));
        $objTemplate->hrefDeleteSkillsOnly = $this->generateFrontendUrl($objPage->row(), '/do/delete_skills_only') . Helper::setQueryString($arrQuery);
        $objTemplate->hrefDeleteCommentsOnly = $this->generateFrontendUrl($objPage->row(), '/do/delete_comments_only') . Helper::setQueryString($arrQuery);
        $objTemplate->hrefPrintTable = $this->generateFrontendUrl($objPage->row(), '/do/print_table') . Helper::setQueryString($arrQuery);
        $objTable = new \FrontendTemplate('voting_table_partial');
        $aRows = \VotingModel::getRows(\Input::get('class'), \Input::get('subject'), \Input::get('teacher'));
        $objTable->rows = $aRows['Datensaetze'];
        $objTable->lastChange = $aRows['lastChange'];
        $objTable->classId = \Input::get('class');
        $objTable->teacherId = \Input::get('teacher');
        $objTable->subjectId = \Input::get('subject');
        $objTable->User = $this->User;

        // Delete row or col href
        $arrQuery = array('teacher' => \Input::get('teacher'), 'subject' => \Input::get('subject'), 'class' => \Input::get('class'));
        $objTable->hrefDeleteRowOrCol = $this->generateFrontendUrl($objPage->row(), '/do/delete_row_or_col') . Helper::setQueryString($arrQuery);

        $objTemplate->classId = \Input::get('class');
        $objTemplate->teacherId = \Input::get('teacher');
        $objTemplate->subjectId = \Input::get('subject');
        $objTemplate->User = $this->User;


        $objTemplate->votingTable = $objTable->parse();

        return $objTemplate;
    }
}
