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
 * Reads and writes classes
 * @package   Models
 * @author    Leo Feyer <https://github.com/leofeyer>
 * @copyright Leo Feyer 2005-2014
 */
class CommentModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_comment';

    /**
     * @param $studentId
     * @param $teacherId
     * @param $subjectId
     * @return \Model\Collection|null
     */
    public static function findComment($studentId, $teacherId, $subjectId)
    {
        $objDb = \Database::getInstance()->prepare("SELECT * FROM tl_comment WHERE student=? AND teacher=? AND subject=?")
            ->execute($studentId, $teacherId, $subjectId);
        return CommentModel::findMultipleByIds($objDb->fetchEach('id'));

    }

    /**
     * @param $student
     * @param $teacher
     * @param $subject
     * @return string
     */
    public static function getCommentModal($student, $teacher, $subject)
    {
        $objModal = new \FrontendTemplate('voting_comment_modal');
        $objStudent = \StudentModel::findByPk($student);
        if ($objStudent !== null)
        {
            $objModal->student = $objStudent->lastname . ' ' . $objStudent->firstname;
            $objModal->studentId = $student;
            $objModal->classname = \StudentModel::getClassnameFromStudentId($student);
            $objModal->subject = $subject;
            $objModal->subjectname = \SubjectModel::getName($subject);

            $objUser = \System::importStatic('FrontendUser');
            $objModal->isAllowed = $teacher == $objUser->id ? true : false;

            $objComment = \Database::getInstance()->prepare('SELECT * FROM tl_comment WHERE student=? AND teacher=? AND subject=? ORDER BY dateOfCreation DESC')->execute($student, $teacher, $subject);
            $objModal->rows = '';

            while ($objComment->next())
            {
                if (!$objModal->isAllowed)
                {
                    $objModal->rows .= '<tr><td><strong>' . \Date::parse('Y-m-d', $objComment->dateOfCreation) . '</strong><br>' . nl2br($objComment->comment) . '</td></tr>';
                }
                else
                {
                    $objPartial = new \FrontendTemplate('voting_comment_modal_row');
                    $objPartial->id = $objComment->id;
                    $objPartial->published = $objComment->published;
                    $objPartial->subject = $objComment->subject;
                    $objPartial->student = $objComment->student;
                    $objPartial->dateOfCreation = \Date::parse('Y-m-d', $objComment->dateOfCreation);
                    $objPartial->comment = nl2br(html_entity_decode($objComment->comment));
                    $objModal->rows .= $objPartial->parse();
                }
            }
        }


        return $objModal->parse();
    }

    /**
     * @param $class
     * @param $teacher
     * @param $subject
     * @return int
     */
    public static function countCommentsFromVotingTable($class, $teacher, $subject)
    {
        $objDb = \Database::getInstance()->prepare('SELECT * FROM tl_comment, tl_student WHERE tl_comment.teacher=? AND  tl_comment.subject=? AND tl_student.id=tl_comment.student AND tl_student.class=?')
            ->execute($teacher, $subject, $class);
        return $objDb->numRows;
    }

    /**
     * @param $intTeacher
     * @param null $intSubject
     * @param null $intClass
     * @param null $intStudent
     * @param string $mode
     * @return mixed|null
     */
    public static function getLastChange($intTeacher, $intSubject, $intClass)
    {

        $objVoting = \Database::getInstance()->prepare('SELECT * FROM tl_comment WHERE teacher = ? AND subject = ? AND student IN (SELECT id FROM tl_student WHERE class = ?) ORDER BY tstamp DESC LIMIT 0,1')
            ->execute($intTeacher, $intSubject, $intClass);


        if ($objVoting->numRows)
        {
            return $objVoting->tstamp;
        }

        return null;
    }

}
