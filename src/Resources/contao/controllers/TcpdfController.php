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
 * Class TcpdfController
 * @package Markocupic\BufBundle
 */
class TcpdfController extends \System
{

    /**
     * @var $objMainController
     */
    protected $objMainController;

    public function __construct($objMainController)
    {
        $this->objMainController = $objMainController;
        $this->import('FrontendUser', 'User');
        $this->import('Database');
        return parent::__construct();
    }

    /**
     * abandoned since 14.01.2017 !!!!!
     * print printAverageTable
     */
    public function printDataSheet()
    {
        // create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Marko Cupic');
        $pdf->SetTitle('Datenblatt von ' . \StudentModel::getFullName(\Input::get('student')));
        $pdf->SetSubject('Beurteilungsbogen');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT);

        // set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->AddPage();

        $pdf->SetFont('helvetica', '', 18);
        $pdf->SetFillColor(255, 255, 255);

        $pdf->Cell(180, 8, 'Beurteilung des Sozial- & Arbeitsverhaltens', 'B', 1, 'L', 0, '', 0);
        $pdf->Ln();

        $pdf->SetFont('helvetica', 'B', 11);
        $posY = $pdf->GetY();
        $pdf->Cell(30, 6, 'Schule: ', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(30, 6, $GLOBALS['TL_CONFIG']['buf_name_school'], 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(30, 6, 'SchülerIn: ', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(30, 6, \StudentModel::getFullName(\Input::get('student')), 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(30, 6, 'Klasse: ', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(30, 6, \StudentModel::getClassnameFromStudentId(\Input::get('student')), 0, 1, 'L');
        $pdf->Ln(3);

        $arrSkills = array(
            'A: selbständig arbeiten',
            'B: sorgfältig arbeiten',
            'C: sich aktiv am Unterricht beteiligen',
            'D: eigene Fähigkeiten einschätzen',
            'E: mit anderen zusammenarbeiten',
            'F: konstruktiv mit Kritik umgehen',
            'G: respektvoll mit anderen umgehen',
            'H: Regeln einhalten',
        );
        $pdf->SetFont('helvetica', '', 9);

        foreach ($arrSkills as $skill)
        {
            $pdf->SetY($posY);
            $pdf->SetX(127);
            $pdf->Cell(8, 4, $skill, 0, 1, 'L');
            $posY += 6;
        }

        $pdf->Ln();

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(10, 10, '', 0, '', 'C');
        $pdf->Cell(50, 10, '', 0);
        $pdf->SetX($pdf->GetX());
        $pdf->Cell(50, 10, '', 0);
        $pdf->SetX($pdf->GetX() + 3);
        $Array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
        $i = 0;
        foreach ($Array as $skill)
        {
            $i += 1;
            $pdf->Cell(9, 10, $skill, 0, '', 'C');
            if ($i == 4)
            {
                $pdf->SetX($pdf->GetX() + 3);
            }
            else
            {
                $pdf->SetX($pdf->GetX());
            }
        }
        //end for
        $pdf->Ln();
        $pdf->SetFont('helvetica', '', 11);

        // do not count zero to the average
        $sql = 'SELECT
		tl_student.lastname, tl_student.firstname,
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
		WHERE tl_student.id = ?
		GROUP BY tl_student.id
		';

        $objDb = $this->Database->prepare($sql)->execute(\Input::get('student'));
        $rows = $objDb->numRows ? $objDb->fetchAllAssoc() : array();
        $color = $m = 0;
        foreach ($rows as $row)
        {
            $m += 1;
            $color += 1;
            if ($color == 2)
            {
                $pdf->SetFillColor(220, 220, 220);
                $color = '0';
            }
            $yPos = $pdf->GetY();

            $pdf->SetY($yPos);
            $pdf->SetX(15);
            $pdf->Cell(10, 7, $m, 1, 1, 'R', 1);

            $pdf->SetY($yPos);
            $pdf->setX(25);
            $pdf->Cell(50, 7, $row['lastname'], 1, 1, 'L', 1);

            $pdf->SetY($yPos);
            $pdf->SetX(75);
            $pdf->Cell(50, 7, $row['firstname'], 1, 1, 'L', 1);

            $pdf->SetY($yPos);
            $pdf->SetX(125 + 3);
            $posX = 128;
            for ($i = 1; $i < 9; $i++)
            {
                $skill = $row['skill' . $i];
                if ($skill === 0 || $skill == 0)
                {
                    $skill = '';
                }

                $pdf->Cell(9, 7, round($skill, 1), 1, '', 'C', 1);

                $pdf->SetY($yPos);
                if ($i == 4)
                {
                    $posX = $posX + 12;
                    $pdf->SetX($posX);
                }
                else
                {
                    $posX = $posX + 9;
                    $pdf->SetX($posX);
                }
            }

            //end for
            $pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
        } //end while

        // Comments
        $pdf->Ln();

        $timeRange = $this->User->showCommentsNotOlderThen * 30 * 24 * 3600;
        if ($timeRange == 0)
        {
            $timeRange = time();
        }
        $timeRange = time() - $timeRange;

        $objComment = \Database::getInstance()->prepare('SELECT * FROM tl_comment WHERE student=? AND published=? AND dateOfCreation>? ORDER BY subject, teacher, dateOfCreation DESC')->execute(\Input::get('student'), 1, $timeRange);
        $prevId = '';
        while ($objComment->next())
        {
            $currentId = $objComment->teacher . '-' . $objComment->subject;
            $pdf->SetFont('helvetica', 'B', 12);
            if ($prevId != $currentId)
            {
                $pdf->Ln();
                $pdf->Cell(180, 8, 'Kommentar von: ' . \TeacherModel::findByPk($objComment->teacher)->firstname . ' ' . \TeacherModel::findByPk($objComment->teacher)->lastname . ', ' . utf8_decode_entities(\SubjectModel::findByPk($objComment->subject)->name), 'B', 1, 'L');
            }
            $pdf->Ln();
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(180, 8, \Date::parse('Y-m-d', $objComment->dateOfCreation), '', 1, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Write(6, utf8_decode_entities($objComment->comment), '', 0, 'L', true, 0, false, false, 0);
            $pdf->Ln();
            $prevId = $currentId;
        }

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Ln();
        $pdf->Ln();

        $pdf->Cell(180, 8, 'Schule Ettiswil, ' . \Date::parse('j. M Y'), 0, 1, 'L');
        $pdf->Output();
        //$pdf->Output('Datenblatt-' . \StudentModel::getFullName(\Input::get('student')) . '.pdf', 'D');

    }

}