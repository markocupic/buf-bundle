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
 * Class AverageTableController
 * @package Markocupic\BufBundle
 */
class AverageTableController extends \Frontend
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
        $objTemplate->classId = \TeacherModel::getOwnClass();
        $objTemplate->rows = $this->getRows();
        //tally sheet link
        $url = $this->generateFrontendUrl($objPage->row(), '/do/print_average_table');
        $objTemplate->printAverageTableLink = $url;
        $objTemplate->printDataSheet = $this->generateFrontendUrl($objPage->row(), '/do/print_data_sheet');
        $objTemplate->printDataSheetDoc = $this->generateFrontendUrl($objPage->row(), '/do/print_data_sheet_doc');


        return $objTemplate;
    }

    /**
     * @return array
     */
    private function getRows()
    {
        // do not count zero to the average
        $sql = 'SELECT
		tl_student.id, tl_student.lastname, tl_student.firstname,
		AVG(CASE WHEN tl_voting.skill1 <> 0 THEN tl_voting.skill1 ELSE NULL END) AS skill1,
		AVG(CASE WHEN tl_voting.skill2 <> 0 THEN tl_voting.skill2 ELSE NULL END) AS skill2,
		AVG(CASE WHEN tl_voting.skill3 <> 0 THEN tl_voting.skill3 ELSE NULL END) AS skill3,
		AVG(CASE WHEN tl_voting.skill4 <> 0 THEN tl_voting.skill4 ELSE NULL END) AS skill4,
		AVG(CASE WHEN tl_voting.skill5 <> 0 THEN tl_voting.skill5 ELSE NULL END) AS skill5,
		AVG(CASE WHEN tl_voting.skill6 <> 0 THEN tl_voting.skill6 ELSE NULL END) AS skill6,
		AVG(CASE WHEN tl_voting.skill7 <> 0 THEN tl_voting.skill7 ELSE NULL END) AS skill7,
		AVG(CASE WHEN tl_voting.skill8 <> 0 THEN tl_voting.skill8 ELSE NULL END) AS skill8
		FROM tl_student
		LEFT JOIN tl_voting ON tl_student.id = tl_voting.student
		WHERE tl_student.class = ? AND tl_student.disable=?
		GROUP BY tl_student.id
		ORDER BY tl_student.gender DESC, tl_student.lastname, tl_student.firstname';

        $objDb = $this->Database->prepare($sql)->execute(\TeacherModel::getOwnClass(), '');
        return $objDb->numRows ? $objDb->fetchAllAssoc() : array();

    }

}
