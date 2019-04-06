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
 * Class BirthdayNotify
 * @package Markocupic\BufBundle
 */
class BirthdayNotify
{

    /**
     * Klassenlehrer benachrichtigen bei Geburtstagen von Schülern
     * Cron birthdayNotify
     */
    public function birthdayNotify()
    {
        $objTeacher = \TeacherModel::findAll();
        if ($objTeacher !== null)
        {
            while ($objTeacher->next())
            {
                if ($objTeacher->enableBirthdayAdvice && $objTeacher->isClassTeacher && $objTeacher->class > 0)
                {
                    $arrMsg = array();

                    $objStudent = \Database::getInstance()->prepare('SELECT * FROM tl_student WHERE class=? AND dateOfBirth > ? AND dateOfBirth < ? AND disable=?')->execute($objTeacher->class, 0, time(), '');
                    while ($objStudent->next())
                    {
                        if (\Date::parse('m.d', time()) === \Date::parse('m.d', $objStudent->dateOfBirth))
                        {
                            $arrMsg[] = array(
                                'title' => sprintf('Nicht vergessen! Heute (%s) ist der Geburtstag von %s. %s wird heute %s Jahre alt.', \Date::parse('d.m.Y', time()), $objStudent->firstname . ' ' . $objStudent->lastname, $objStudent->firstname, \Date::parse('Y', time()) - \Date::parse('Y', $objStudent->dateOfBirth))
                            );
                        }
                    }

                    // Send Email
                    if (count($arrMsg) > 0)
                    {
                        $objEmail = new \Email();
                        $objEmailTemplate = new \FrontendTemplate('mail_advice_klp');
                        $objEmailTemplate->headline1 = 'Geburtstagsreminder';
                        $objEmailTemplate->headline2 = 'In deiner Klasse hat heute eine Schülerin/ein Schüler Geburtstag';
                        $objEmailTemplate->rows = $arrMsg;
                        $objEmail->html = $objEmailTemplate->parse();
                        $objEmail->subject = 'Geburtstagsreminder';
                        $objEmail->from = 'webadmin@' . \Environment::get('host');
                        $objEmail->sendTo($objTeacher->email);
                        \System::log(\TeacherModel::getFullName($objTeacher->id) . ' wurde per email über Geburtstage von Schülern in seiner Klasse benachrichtigt.', __METHOD__, TL_GENERAL);
                    }
                }
            }
        }

        \Database::getInstance()->prepare('UPDATE tl_comment %s')->set(array('adviced' => true))->execute();
    }

}