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
 * Class TallySheetController
 * Front end module buf
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class TallySheetController extends \Frontend
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
        $arrData = $this->getTableBody();
        $objTemplate->rows = $arrData['rows'];
        $objTemplate->js = $arrData['js'];
        //print tally sheet link
        $objTemplate->printTallySheetLink = $this->generateFrontendUrl($objPage->row(), '/do/print_tally_sheet');

        return $objTemplate;
    }

    /**
     * @return array
     */
    private function getTableBody()
    {
        $output = "";
        $js = "";
        $cellId = 0;

        $Klasse = \TeacherModel::getOwnClass();

        $output .= "\r\n\r\n";
        $objStudent = $this->Database->prepare('SELECT * FROM tl_student WHERE class = ? AND disable = ? ORDER BY gender DESC, lastname, firstname')->execute($Klasse, '');
        while ($objStudent->next())
        {
            $output .= '<tr>';
            $output .= '<td class="align_left">' . $objStudent->lastname . '</td>';
            $output .= '<td class="align_left">' . $objStudent->firstname . '</td>';

            for ($i = 1; $i < 9; $i++)
            {
                // get the skill average
                $objAverage = $this->Database->prepare("SELECT AVG(skill$i) AS 'average' FROM tl_voting WHERE student = ? AND skill$i > 0")->execute($objStudent->id);
                $rowAverage = $objAverage->fetchAssoc();
                $skillAverage = \VotingModel::getAverage($objStudent->id, $i, 0);

                $class = ($i % 2 != 0) ? 'tallycell textaligncenter odd' : 'tallycell textaligncenter even';

                for ($m = 1; $m < 5; $m++)
                {
                    //Die Zelle mit dem Durchschnitt wird farblich hervorgehoben
                    $tdClass = ($skillAverage == $m) ? $class . ' bg_red' : $class;
                    $cellId++;
                    $output .= sprintf('<td id="Zelle_%s" title="&oslash; avg: %s" class="%s">', $cellId, \VotingModel::getAverage($objStudent->id, $i, 1), $tdClass);
                    if ($rowAverage["average"])
                    {
                        $js .= sprintf("
$('#Zelle_%s').on('click', function(event) {
       event.stopPropagation();
       objTallySheet.showInfoBox(this,%s,%s);
});
                    ", $cellId, $objStudent->id, $i);
                    }

                    $objVoting = $this->Database->prepare("SELECT * FROM tl_voting WHERE student = ? AND skill$i = ?")->execute($objStudent->id, $m);
                    $br = 0;
                    while ($objVoting->next())
                    {
                        $output .= ($objVoting->teacher == $this->User->id) ? '<span style="text-decoration:underline; font-weight:bold; font-size:1em;">I</span>' : 'I';
                        $br++;
                        if ($br == 5)
                        {
                            $br = 0;
                            $output .= '<br>';
                        }
                    }
                    $output .= '</td>' . "\r\n";
                }
                //end for
                unset($HintergrundGeradeSpalten);
            }
            //end for
            $output .= "</tr>\r\n";
        } //end while

        return array("js" => $js, "rows" => $output);
    }

}
