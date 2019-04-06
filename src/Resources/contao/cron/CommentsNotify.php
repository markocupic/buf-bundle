<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2019 Leo Feyer
 * @package BUF (Beurteilen und Fördern)
 * @author Marko Cupic m.cupic@gmx.ch, 2014-2019
 * @link https://github.com/markocupic/buf-bundle
 * @license MIT
 */

namespace Markocupic\BufBundle;

/**
 * Class CommentsNotify
 * @package Markocupic\BufBundle
 */
class CommentsNotify
{

    /**
     * Klassenlehrer benachrichtigen bei neuen Kommentaren
     * Cron notifyOnNewComments
     */
    public function notifyOnNewComments()
    {
        $objTeacher = \TeacherModel::findAll();
        if ($objTeacher !== null)
        {
            while ($objTeacher->next())
            {
                if ($objTeacher->adviceOnNewComments && $objTeacher->isClassTeacher && $objTeacher->class > 0)
                {
                    $arrMsg = array();
                    $objCom = \Database::getInstance()->prepare('SELECT * FROM tl_comment WHERE tl_comment.student IN (SELECT id FROM tl_student WHERE tl_student.class=? AND tl_student.disable=?) AND tl_comment.adviced=? ORDER BY tl_comment.student')->execute($objTeacher->class, '', '');
                    while ($objCom->next())
                    {
                        $arrMsg[] = array(
                            'title' => 'Neuer Kommentar von ' . \TeacherModel::getFullName($objCom->teacher) . ' zu ' . \StudentModel::getFullName($objCom->student) . ' im Fach ' . \SubjectModel::getName($objCom->subject),
                            'body'  => $objCom->comment,
                        );
                    }

                    // Send Email
                    if (count($arrMsg) > 0)
                    {
                        $objEmail = new \Email();
                        $objEmailTemplate = new \FrontendTemplate('mail_advice_klp');
                        $objEmailTemplate->headline1 = 'Beurteilen <br>und F&ouml;rdern';
                        $objEmailTemplate->headline2 = 'Neue oder aktualisierte Kommentare an Sch&uuml;lern deiner Klasse';
                        $objEmailTemplate->rows = $arrMsg;
                        $objEmail->html = $objEmailTemplate->parse();
                        $objEmail->subject = 'Neue oder aktualisierte Kommentare im Bewertungstool vorhanden';
                        $objEmail->from = 'webadmin@' . \Environment::get('host');
                        $objEmail->sendTo($objTeacher->email);
                        \System::log(\TeacherModel::getFullName($objTeacher->id) . ' wurde per email ueber neue oder veränderte Kommentare benachrichtigt.', __METHOD__, TL_GENERAL);
                    }
                }
            }
        }

        \Database::getInstance()->prepare('UPDATE tl_comment %s')->set(array('adviced' => true))->execute();
    }

}