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
 * Class FpdfController
 * Front end module buf
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class FpdfController extends \System
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
        define('FPDF_FONTPATH', TL_ROOT . '/system/modules/buf/plugins/fpdf/font/');

        // register fpdf classes
        \ClassLoader::addClasses(array('FPDF' => 'system/modules/buf/plugins/fpdf/fpdf.php', 'CellPDF' => 'system/modules/buf/plugins/fpdf/addOns/cellpdf.php'));

        return parent::__construct();
    }

    /**
     * print voting table
     */
    public function printTable()
    {

        $teacher = \Input::get('teacher');
        $class = \Input::get('class');
        $subject = \Input::get('subject');


        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->setSubject('Beurteilungstabelle', true);
        $pdf->setTitle('Beurteilungstabelle');

        $pdf->setAuthor(\TeacherModel::getFullname($teacher));

        //$pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 18);
        $pdf->SetFillColor(255, 255, 255);

        $pdf->Cell(1140, 8, 'Beurteilung des Sozial- & Arbeitsverhaltens', 'B', '', 'L');
        $pdf->Ln();
        $pdf->Ln();
        $XPos = $pdf->getX();
        $YPos = $pdf->getY();
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(30, 6, 'Schule: ', 0, '', 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(30, 6, utf8_decode($GLOBALS['TL_CONFIG']['buf_name_school']), 0, '', 'L');
        $pdf->Ln();

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(30, 6, 'Lehrperson: ', 0, '', 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(30, 6, utf8_decode(\TeacherModel::getFullName($teacher)), 0, '', 'L');

        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(30, 6, 'Klasse: ', 0, '', 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(30, 6, utf8_decode(\ClassModel::getName($class)), 0, '', 'L');

        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(30, 6, 'Fach: ', 0, '', 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(30, 6, utf8_decode(\SubjectModel::getName($subject)), 0, '', 'L');
        $pdf->Ln();

        $pdf->setY($YPos);
        $pdf->setX(140);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(10, 4, utf8_decode('A: selbständig arbeiten'), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(140);
        $pdf->Cell(10, 4, utf8_decode('B: sorgfältig arbeiten'), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(140);

        $pdf->Cell(10, 4, utf8_decode('C: sich aktiv am Unterricht beteiligen'), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(140);

        $pdf->Cell(10, 4, utf8_decode('D: eigene Fähigkeiten einschätzen'), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(140);

        $pdf->Cell(10, 4, utf8_decode('E: mit anderen zusammenarbeiten'), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(140);

        $pdf->Cell(10, 4, utf8_decode('F: konstruktiv mit Kritik umgehen'), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(140);

        $pdf->Cell(10, 4, utf8_decode('G: respektvoll mit anderen umgehen'), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(140);

        $pdf->Cell(10, 4, utf8_decode('H: Regeln einhalten'), 0, '', 'L');
        $pdf->Ln();
        $pdf->Ln();


        $pdf->SetFont('Arial', 'B', 16);
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
        $pdf->SetFont('Arial', '', 11);

        $objStudent = $this->Database->prepare('SELECT * FROM tl_student WHERE disable=? AND class=? ORDER BY gender DESC, lastname ASC, firstname ASC')->execute('', $class);
        $color = $m = 0;
        while ($objStudent->next())
        {
            $objVoting = $this->Database->prepare('SELECT * FROM tl_voting WHERE teacher = ? AND student = ? AND subject = ?')->execute($teacher, $objStudent->id, $subject);

            $m += 1;
            $color += 1;
            if ($color == 2)
            {
                $pdf->SetFillColor(220, 220, 220);
                $color = '0';
            }

            $pdf->Cell(10, 7, $m, 1, '', 'R', 1);
            $pdf->Cell(50, 7, utf8_decode($objStudent->lastname), 1, '', 'L', 1);
            $pdf->SetX($pdf->GetX());
            $pdf->Cell(50, 7, utf8_decode($objStudent->firstname), 1, '', 'L', 1);
            $pdf->SetX($pdf->GetX() + 3);

            for ($i = 1; $i < 9; $i++)
            {
                $skill = $objVoting->{'skill' . $i};
                if ($skill === 0 || $skill == 0)
                {
                    $skill = '';
                }
                $pdf->Cell(9, 7, $skill, 1, '', 'C', 1);
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
            $pdf->SetFillColor(255, 255, 255);
        } //end while
        $pdf->Ln();

        // Display comments
        //$pdf->SetFont('Arial', 'B', 18);
        //$pdf->Cell(190, 8, 'Kommentare', 0, '', 'L');
        $pdf->Ln();

        $objStudent = \Database::getInstance()->prepare('SELECT * FROM tl_student WHERE disable=? AND class=? ORDER BY gender, lastname, firstname')->execute('', $class);
        while ($objStudent->next())
        {
            $objComment = \Database::getInstance()->prepare('SELECT * FROM tl_comment WHERE subject=? AND student=? AND teacher=? AND published=?')->execute($subject, $objStudent->id, $teacher, 1);
            while ($objComment->next())
            {
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(190, 8, utf8_decode($objStudent->lastname . ' ' . $objStudent->firstname) . ', (' . \Date::parse('d.m.Y', $objComment->tstamp) . ')', 'B', '', 'L');
                $pdf->Ln();
                $pdf->SetFont('Arial', '', 11);
                $pdf->Write(6, html_entity_decode(utf8_decode($objComment->comment)));
                $pdf->Ln();
                $pdf->Ln();
            }
        }

        $pdf->SetFont('Arial', '', 11);
        $pdf->Ln();
        $pdf->Ln();

        $pdf->Cell(190, 8, \Date::parse('j. M Y') . ',  Unterschrift: _________________________________________', 0, '', 'L');
        $pdf->Output();
    }

    
    /**
     * print tally sheet
     */
    public function printTallySheet()
    {


        $ID_Klasse = \TeacherModel::getOwnClass();
        $ID_LP = $this->User->id;

        $bgcolor = array(
            'white' => array(255, 255, 255), 'grey' => array(220, 220, 220), 'red' => array(255, 150, 150)
        );


        //Wichtig um die Zeilenhöhe in der Strichliste zu ermitteln
        $MaxAnzahlStriche = array();
        $objStudent = \Database::getInstance()->prepare("SELECT * FROM tl_student WHERE disable=? AND class = ? ORDER BY gender DESC,lastname,firstname")->execute('', $ID_Klasse);
        while ($objStudent->next())
        {
            for ($i = 1; $i < 9; $i++)
            {
                for ($Niveau = 1; $Niveau < 5; $Niveau++)
                {
                    $objVoting = \Database::getInstance()->prepare('SELECT * FROM tl_voting WHERE student = ? AND skill' . $i . ' = ?')->execute($objStudent->id, $Niveau);
                    array_push($MaxAnzahlStriche, $objVoting->numRows);
                }
            }
        }
        rsort($MaxAnzahlStriche);
        $AnzahlNoetigeZeilen = (int)($MaxAnzahlStriche[0] / 5 + 1);
        $cellHeight = 3 * $AnzahlNoetigeZeilen + 2;
        if ($cellHeight < 6)
        {
            $cellHeight = 6;
        }
        //Ende Zeilenhöhe


        //Instanzierung
        $pdf = new \CellPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 18);
        $pdf->SetFillColor($bgcolor['white'][0], $bgcolor['white'][1], $bgcolor['white'][2]);

        $pdf->Cell(280, 8, "Beurteilung des Sozial- & Arbeitsverhaltens (Strichliste)", 'B', '', 'L');
        $pdf->Ln();
        $pdf->Ln();
        $XPos = $pdf->getX();
        $YPos = $pdf->getY();
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(40, 6, "Schule: ", 0, '', 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(30, 6, utf8_decode($GLOBALS['TL_CONFIG']['buf_name_school']), 0, '', 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(40, 6, "Klasse: ", 0, '', 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(30, 6, utf8_decode(\ClassModel::getName($ID_Klasse)), 0, '', 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(40, 6, "Klassenlehrperson: ", 0, '', 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(30, 6, utf8_decode(\TeacherModel::getFullName($ID_LP)), 0, '', 'L');

        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Ln();

        $pdf->setY($YPos);
        $pdf->setX(140);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(60, 4, utf8_decode("A: selbständig arbeiten"), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(140);
        $pdf->Cell(60, 4, utf8_decode("B: sorgfältig arbeiten"), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(140);

        $pdf->Cell(60, 4, utf8_decode("C: sich aktiv am Unterricht beteiligen"), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(140);

        $pdf->Cell(60, 4, utf8_decode("D: eigene Fähigkeiten einschätzen"), 0, '', 'L');
        $pdf->Ln();

        $pdf->setY($YPos);

        $pdf->setX(220);
        $pdf->Cell(60, 4, utf8_decode("E: mit anderen zusammenarbeiten"), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(220);

        $pdf->Cell(60, 4, utf8_decode("F: konstruktiv mit Kritik umgehen"), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(220);

        $pdf->Cell(60, 4, utf8_decode("G: respektvoll mit anderen umgehen"), 0, '', 'L');
        $pdf->Ln();
        $pdf->setX(220);

        $pdf->Cell(60, 4, utf8_decode("H: Regeln einhalten"), 0, '', 'L');
        $pdf->Ln();
        $pdf->Ln();


        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetX(73);
        $Array = array("A", "B", "C", "D", "E", "F", "G", "H");
        foreach ($Array as $Kriterium)
        {
            $pdf->Cell(24, 9, $Kriterium, 0, '', 'C');
            $pdf->SetX($pdf->GetX() + 3);
        }
        //end for
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 9);
        $objStudent = \Database::getInstance()->prepare('SELECT * FROM tl_student WHERE disable=? AND class = ? ORDER BY gender DESC,lastname,firstname')->execute('', $ID_Klasse);
        $color = $m = 0;
        while ($objStudent->next())
        {
            $m += 1;
            $color += 1;
            if ($color == 2)
            {
                $pdf->SetFillColor($bgcolor['grey'][0], $bgcolor['grey'][0], $bgcolor['grey'][0]);
                $color = 0;
            }


            $pdf->Cell(6, $cellHeight, $m, 1, '', 'R', 1);
            $pdf->Cell(27, $cellHeight, utf8_decode($objStudent->lastname), 1, '', 'L', 1);
            $pdf->SetX($pdf->GetX());
            $pdf->Cell(27, $cellHeight, utf8_decode($objStudent->firstname), 1, '', 'L', 1);
            $Y = $pdf->getY();
            $X = 26;
            for ($i = 1; $i < 9; $i++)
            {
                $pdf->SetX($pdf->getX() + 3);
                for ($Niveau = 1; $Niveau < 5; $Niveau++)
                {
                    $objVoting = \Database::getInstance()->prepare('SELECT * FROM tl_voting WHERE student = ? AND skill' . $i . ' = ?')->execute($objStudent->id, $Niveau);
                    $str_Striche = "";
                    $anz = 0;
                    while ($objVoting->next())
                    {
                        $anz++;
                        if ($anz % 5 == 0)
                        {
                            $str_Striche .= " \n";
                        }

                        if ($ID_LP == $objVoting->teacher)
                        {
                            $str_Striche .= 'I';
                        }
                        else
                        {
                            $str_Striche .= 'i';
                        }
                    }
                    if (\VotingModel::getAverage($objStudent->id, $i, 0) == $Niveau)
                    {
                        $pdf->SetFillColor($bgcolor['red'][0], $bgcolor['red'][1], $bgcolor['red'][2]);
                    }
                    else
                    {
                        if ($color == 1)
                        {
                            $pdf->SetFillColor($bgcolor['white'][0], $bgcolor['white'][1], $bgcolor['white'][2]);
                        }
                        else
                        {
                            $pdf->SetFillColor($bgcolor['grey'][0], $bgcolor['grey'][1], $bgcolor['grey'][2]);
                        }
                    }
                    $pdf->SetFont('Arial', '', 9);
                    $pdf->Cell(6, $cellHeight, $str_Striche, 1, 0, 'L', 1);
                    $X += 6;
                }
            }
            //end for
            //$pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Ln();
        } //end while
        $pdf->Cell(190, 8, "Wertungen der Klassenlehrperson sind mit einem grossen I, Wertungen von Fachlehrpersonen mit einem kleinen i gekennzeichnet.", 0, '', 'L');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(190, 11, date("j. M Y") . ",  Unterschrift: _________________________________________", 0, '', 'L');
        $pdf->Output();
    }

}