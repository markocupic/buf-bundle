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
 * Reads and writes classes
 * @package   Models
 * @author    Leo Feyer <https://github.com/leofeyer>
 * @copyright Leo Feyer 2005-2014
 */
class VotingModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_voting';


    /**
     * @param $classId
     * @param $subjectId
     * @param $teacherId
     * @return array
     */
    public static function getRows($classId, $subjectId, $teacherId)
    {

        $objUser = \System::importStatic('FrontendUser');
        $tolerance = $objUser->deviation;

        $arr_datensaetze = array();

        $objStudent = \Database::getInstance()->prepare('SELECT id, lastname, firstname FROM tl_student WHERE class=? AND disable=? ORDER BY gender DESC,lastname, firstname')->execute($classId,'');
        while ($objStudent->next())
        {
            $m = 'student_' . $objStudent->id;
            $objVoting = \Database::getInstance()->prepare('SELECT * FROM tl_voting WHERE teacher = ? AND student = ? AND subject = ?')->execute($teacherId, $objStudent->id, $subjectId);
            //Falls die Abfrage nicht eindeutig war.
            if ($objVoting->numRows > 1)
            {
                die ("Abbruch: zwei Datensätze der selben Kategorie vorhanden");
            }

            //Falls zum Namen keine Bewertung gefunden wurde, bleiben die Zellen leer.
            if ($objVoting->numRows < 1)
            {
                for ($i = 1; $i < 9; $i++)
                {
                    $skill = "0";
                    $deviation = "";
                    $color = "000000";
                    $array_name = "unterarray" . $i;
                    $$array_name = array(
                        "value"     => $skill,
                        "deviation" => $deviation,
                        "color"     => $color,
                        "average"   => 0,
                        "date"      => null,
                        "tstamp"    => null,
                    );
                }
            }
            //Falls ein Datensatz vorhanden ist
            if ($objVoting->numRows == 1)
            {
                $dataRecord = $objVoting->fetchAssoc();
                $tstamp = $dataRecord["tstamp"];
                for ($i = 1; $i < 9; $i++)
                {
                    $skill = $dataRecord["skill" . $i];
                    if ($skill < 1)
                    {
                        $skill = "0";
                    }
                    //average
                    //Nur für den Klassenlehrer an seiner Stammklasse ersichtlich
                    if ($skill > 0 && \TeacherModel::getOwnClass() == $classId)
                    {
                        $sql = sprintf('SELECT AVG(skill%s) AS average FROM tl_voting WHERE student = ? AND skill%s > 0 AND skill%s < 5 AND id != ?', $i, $i, $i);
                        $stmt3 = \Database::getInstance()->prepare($sql)->execute($objStudent->id, $dataRecord['id']);
                        $rowAverage = $stmt3->fetchAssoc();
                        $intAverage = $rowAverage['average'];
                        $deviation = $dataRecord["skill" . $i] - $intAverage;
                        if ($deviation < (-1) * $tolerance && $intAverage > 0)
                        {
                            $color = "009900";
                        }
                        elseif ($deviation > $tolerance && $intAverage > 0)
                        {
                            $color = "CC0000";
                        }
                        else
                        {
                            $color = "000000";
                        }
                    }
                    else
                    {
                        $intAverage = 0;
                        $color = "000000";
                    }

                    if ($color != "000000" && $intAverage > 0)
                    {
                        $deviation = round($deviation, 1);
                    }
                    else
                    {
                        $deviation = "";
                    }
                    $array_name = "unterarray" . $i;
                    $$array_name = array(
                        "value"     => $skill,
                        "deviation" => $deviation,
                        "color"     => $color,
                        "average"   => $intAverage,
                        "date"      => \Date::parse('d.m.Y', $tstamp),
                        "tstamp"    => $tstamp,
                    );

                }
                //end for
            }
            //end if

            $dataRecordId = isset($dataRecord) ? $dataRecord['id'] : null;
            unset($dataRecord);
            $arr_datensaetze[$m] = array(
                "student"      => $objStudent->id,
                "lastname"     => $objStudent->lastname,
                "firstname"    => $objStudent->firstname,
                "dataRecordId" => $dataRecordId,
                "skill1"       => $unterarray1,
                "skill2"       => $unterarray2,
                "skill3"       => $unterarray3,
                "skill4"       => $unterarray4,
                "skill5"       => $unterarray5,
                "skill6"       => $unterarray6,
                "skill7"       => $unterarray7,
                "skill8"       => $unterarray8,
            );
        }
        //end while
        return array(
            'Datensaetze' => $arr_datensaetze,
            'lastChange'  => self::getLastChange($teacherId, $subjectId, $classId),
        );
    }

    /**
     * @param $mode
     * @param $colOrRow
     * @param $teacher
     * @param $subject
     * @param $class
     * @return bool
     */
    public static function deleteRowOrCol($mode, $colOrRow, $teacher, $subject, $class)
    {

        $objUser = \System::importStatic('FrontendUser');

        if ($objUser->id == $teacher)
        {
            if ($mode == 'delete_row')
            {
                \Database::getInstance()->prepare('DELETE FROM tl_voting WHERE teacher=? AND student=? AND subject=?')->execute($teacher, $colOrRow, $subject);
                //\Database::getInstance()->prepare('DELETE FROM tl_comment WHERE teacher=? AND student=? AND subject=?')->execute($teacher, $colOrRow, $subject);
                return true;
            }

            if ($mode == 'delete_col')
            {

                $set = array('tstamp' => time());
                $set['skill' . $colOrRow] = '0';
                \Database::getInstance()->prepare('UPDATE tl_voting %s WHERE teacher=? AND subject=? AND student = ANY (SELECT id FROM tl_student WHERE class=?)')->set($set)->execute($teacher, $subject, $class);
                // Delete all empty rows
                \Database::getInstance()->execute('DELETE FROM tl_voting WHERE (skill1 + skill2 + skill3 + skill4 + skill5 + skill6 + skill7 + skill8) < 1');

                return true;
            }
        }
        return false;
    }

    /**
     * @param $student
     * @param $class
     * @param $teacher
     * @param $subject
     * @param $skill
     * @param int $value
     * @return bool|int|string
     */
    public static function update($student, $teacher, $subject, $skill, $value = 0)
    {

        $objUser = \System::importStatic('FrontendUser');
        if (intval($teacher) == $objUser->id)
        {
            $value = trim($value);
            $value = $value == '' ? 0 : $value;
            if (preg_match('/^[0-4]{0,1}$/', $value))
            {
                $objVoting = \VotingModel::find(array(
                        'column' => array('tl_voting.teacher=?', 'tl_voting.student=?', 'tl_voting.subject=?'),
                        'value'  => array($teacher, $student, $subject),
                        'limit'  => 1,
                    ));

                if ($objVoting !== null)
                {
                    $objVoting->{'skill' . $skill} = $value;
                    $objVoting->tstamp = time();
                    $objVoting->save();
                    // delete all empty rows
                    \Database::getInstance()->prepare('DELETE FROM tl_voting WHERE id=? AND (skill1 + skill2 + skill3 + skill4 + skill5 + skill6 + skill7 + skill8) < 1')->execute($objVoting->id);
                }
                else
                {
                    if ($value > 0)
                    {
                        $objVoting = new \VotingModel();
                        $objVoting->student = $student;
                        $objVoting->teacher = $teacher;
                        $objVoting->subject = $subject;
                        $objVoting->{'skill' . $skill} = $value;
                        $objVoting->tstamp = time();
                        $objVoting->save();
                    }
                }
                if ($value == 0)
                {
                    $value = '';
                }

                return $value;
            }
        }
        return false;
    }

    /**
     * @param $studentId
     * @param $skillId
     * @return string
     */
    public static function getInfoBox($studentId, $skillId)
    {

        if (!\TeacherModel::getOwnClass())
        {
            return false;
        }
        $objModal = new \FrontendTemplate('tallysheet_modal');
        $rows = '';

        $objVoting = \Database::getInstance()->prepare('SELECT * FROM tl_voting WHERE student = ? AND skill' . $skillId . ' > ? ORDER BY id')->execute($studentId, '0');
        if ($objVoting->numRows)
        {
            $i = 0;
            while ($objVoting->next())
            {
                $skill = 'skill' . $skillId;
                $i++;
                if ($i == 1)
                {
                    $objModal->skill = $GLOBALS['TL_LANG']['tl_voting']['skill' . $skillId][0];
                    $objModal->student = \StudentModel::findByPk($studentId)->firstname . ' ' . \StudentModel::findByPk($studentId)->lastname;
                }
                $rows .= '<tr><td><strong><span class="red strong">' . $objVoting->$skill . '&nbsp;</span></strong></td><td class="green strong">&nbsp;' . \SubjectModel::findByPk($objVoting->subject)->acronym . '&nbsp;</td><td class="strong">&nbsp;' . substr(\TeacherModel::findByPk($objVoting->teacher)->firstname, 0, 1) . '. ' . \TeacherModel::findByPk($objVoting->teacher)->lastname . '</td></tr>';

            }
            $objModal->rows = $rows;
            $html = $objModal->parse();
            return $html;
        }

    }


    /**
     * @return bool
     */
    public static function isOwner()
    {

        $objUser = \System::importStatic('FrontendUser');
        if ($objUser->id == \Input::get('teacher'))
        {
            return true;
        }
        return false;
    }

    /**
     * @param $intStudent
     * @param $intCol
     * @param int $precision
     * @return number
     */
    public static function getAverage($intStudent, $intCol, $precision = 0)
    {

        $objAverage = \Database::getInstance()->prepare("SELECT AVG(skill$intCol) AS 'average' FROM tl_voting WHERE student = ? AND skill$intCol > 0")->execute($intStudent);
        $rowAverage = $objAverage->fetchAssoc();
        // if the average is 1.5 or 2.5 or 3.5, then always round down in favor of the student
        return abs(round($rowAverage["average"] - 0.0000001, $precision));
    }

    /**
     * @param $intTeacher
     * @param null $intSubject
     * @param null $intClass
     * @param null $intStudent
     * @param string $mode
     * @return mixed|null
     */
    public static function getLastChange($intTeacher, $intSubject = null, $intClass = null, $intStudent = null, $mode = 'table')
    {

        if ($mode == 'table')
        {
            $objVoting = \Database::getInstance()->prepare('SELECT * FROM tl_voting WHERE teacher = ? AND subject = ? AND student IN (SELECT id FROM tl_student WHERE class = ?) ORDER BY tstamp DESC LIMIT 0,1')->execute($intTeacher, $intSubject, $intClass);
        }
        elseif ($mode == 'teacher')
        {
            // $mode= 'teacher'
            $objVoting = \Database::getInstance()->prepare('SELECT * FROM tl_voting WHERE teacher = ? ORDER BY tstamp DESC LIMIT 0,1')->execute($intTeacher, $intStudent, $intSubject);
        }
        else
        {
            // $mode= 'row'
            $objVoting = \Database::getInstance()->prepare('SELECT * FROM tl_voting WHERE teacher = ? AND student = ? AND subject = ? ORDER BY tstamp DESC')->execute($intTeacher, $intStudent, $intSubject);
        }

        if ($objVoting->numRows)
        {
            return $objVoting->tstamp;
        }
        return null;
    }

    /**
     * @param $start
     * @param $end
     * @return array
     */
    public static function getDatesFromRange($startDate, $endDate)
    {
        $interval = new \DateInterval('P1D');

        $realEnd = new \DateTime($endDate);
        $realEnd->add($interval);

        $period = new \DatePeriod(new \DateTime($startDate), $interval, $realEnd);

        foreach ($period as $date)
        {
            $array[] = $date->format('Y-m-d');
        }

        return $array;
    }


    /**
     * @param $teacherId
     * @param int $startTstamp
     * @param int $endTstamp
     * @return array
     */
    public static function getVotingsAsJSON($teacherId, $startTstamp = 0, $endTstamp = 0)
    {
        // 180 d
        $range = 180 * 24 * 60 * 60;
        if ($endTstamp <= $startTstamp)
        {
            $endTstamp = time();
        }

        if ($startTstamp == 0)
        {
            $startTstamp = $endTstamp - $range;
        }

        $arrDates = self::getDatesFromRange(\Date::parse('Y-m-d', $startTstamp), \Date::parse('Y-m-d', $endTstamp));

        $arrDat = array();
        foreach ($arrDates as $v)
        {
            $arrDat[$v] = array('x' => $v, 'y' => '0');
        }
        $objDb = \Database::getInstance()->prepare("
            SELECT
            COUNT(id) as order_count,
            DATE(FROM_UNIXTIME(tstamp)) as order_date
            FROM `tl_voting`
            WHERE tstamp > ? AND student IN (SELECT id FROM tl_student WHERE class = ? AND disable = ?)
            GROUP BY order_date
        ")->execute($startTstamp, \TeacherModel::getOwnClass($teacherId), '');

        while ($objDb->next())
        {
            $arrDat[$objDb->order_date] = array("x" => $objDb->order_date, "y" => $objDb->order_count);
        }
        return array_values($arrDat);
    }


}
