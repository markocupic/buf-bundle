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

class BufHelper extends \Controller
{

    /**
     * Klassenlehrer benachrichtigen bei neuen Kommentaren
     * generatePage-Hook
     */
    public function adviceOnNewComments()
    {
        $objTeacher = \TeacherModel::findAll();
        if ($objTeacher !== null)
        {
            while ($objTeacher->next())
            {
                if ($objTeacher->adviceOnNewComments && $objTeacher->isClassTeacher && $objTeacher->class > 0)
                {
                    $arrMsg = array();
                    $objCom = \Database::getInstance()->prepare('SELECT * FROM tl_comment WHERE tl_comment.student IN (SELECT id FROM tl_student WHERE tl_student.class=? AND tl_student.disable=?) AND tl_comment.adviced=? ORDER BY tl_comment.student')->execute($objTeacher->class,'','');
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
                        $objEmailTemplate->rows = $arrMsg;
                        $objEmail->html = $objEmailTemplate->parse();
                        $objEmail->subject = 'Neue oder aktualisierte Kommentare im Bewertungstool vorhanden';
                        $objEmail->from = 'admin@' . \Environment::get('host');
                        $objEmail->sendTo($objTeacher->email);
                        \System::log(\TeacherModel::getFullName($objTeacher->id) . ' wurde per email ueber neue oder veränderte Kommentare benachrichtigt.', __METHOD__, TL_GENERAL);
                    }
                }
            }
        }

        \Database::getInstance()->prepare('UPDATE tl_comment %s')->set(array('adviced' => true))->execute();

    }

    /**
     * Contao replaceInsertTags Callback
     * @param $strTag
     * @return bool
     */
    public function bufReplaceInsertTags($strTag)
    {
        global $objPage;
        if ($strTag == 'buf::name_school')
        {
            if (trim($GLOBALS['TL_CONFIG']['buf_name_school']) != '')
            {
                return '<li><span class="fa fa-building-o"></span> ' . $GLOBALS['TL_CONFIG']['buf_name_school'] . '</li>';
            }
        }

        if ($strTag == 'buf::dashboard_link')
        {
            $url = \Frontend::generateFrontendUrl($objPage->row(), '/do/dashboard');
            return sprintf('<a href="%s" title="zurück zum Dashboard"><span class="fa fa-arrow-left"></span> Zurück zum Dashboard</a>', $url);
        }

        if ($strTag == 'buf::logged_in_user')
        {
            if (FE_USER_LOGGED_IN)
            {
                $user = \FrontendUser::getInstance();
                return '<li><span class="fa fa-user"></span> ' . $user->firstname . ' ' . $user->lastname . '</li>';
            }
        }

        if ($strTag == 'buf::logout_user')
        {
            // Login and redirect
            $page = \PageModel::findByAlias('buf');

            if (FE_USER_LOGGED_IN)
            {
                $url = \Frontend::generateFrontendUrl($page->row(), '/do/login') . Helper::setQueryString(array('act' => 'logout'));
                return '<li><a href="' . $url . '" title="Abmelden"><span class="fa fa-sign-out-alt"></span></a></li>';
            }
            else
            {
                $url = \Frontend::generateFrontendUrl($page->row(), '/do/login');
                return '<li><a href="' . $url . '" title="Anmelden"><span class="fa fa-sign-in-alt"></span></a></li>';
            }
        }

        if ($strTag == 'buf::dashboard_link_header')
        {
            // Dashboard link in header
            $page = \PageModel::findByAlias('buf');

            if (FE_USER_LOGGED_IN)
            {
                $url = \Frontend::generateFrontendUrl($page->row(), '/do/dashboard');
                return '<li><a href="' . $url . '" title="Gehe zum Dashboard"><span class="fa fa-list"></span></a></li>';
            }
        }

        if ($strTag == 'buf::account_settings')
        {
            // Login and redirect
            if (FE_USER_LOGGED_IN)
            {
                $url = \Frontend::generateFrontendUrl($objPage->row(), '/do/account_settings');
                return '<li><a href="' . $url . '" title="Konto Einstellungen"><span class="fa fa-cogs"></span></a></li>';
            }
        }
        return false;
    }

    /**
     * ensure referential integrity in tables with multiple parent tables
     * method called as reviseTableHook
     *
     * in parent tables:
     * add one ore more child tables in the config section of the DCA
     * $GLOBALS['TL_DCA']['tl_parent']['config']['buf_ctable'] = array('tl_child', 'tl_child2');
     *
     * in child tables:
     * add one ore more parent tables in the config section of the DCA
     * $GLOBALS['TL_DCA']['tl_child']['config']['buf_ptable'] = array('tl_parent', 'tl_parent2');
     * add the foreign key in the fields section of the DCA
     * $GLOBALS['TL_DCA']['tl_child']['fields']['someField1']['buf_linksTo'] = 'tl_parent.id';
     * $GLOBALS['TL_DCA']['tl_child']['fields']['someField2']['buf_linksTo'] = 'tl_parent2.id';
     *
     * @param string $table
     * @param string $new_records
     * @param string $parent_table
     * @param string $child_tables
     * @return bool
     */
    public static function checkForReferentialIntegrity($table = '', $new_records = '', $parent_table = '', $child_tables = '')
    {


        $reload = false;

        $db = \Database::getInstance();

        // Check for a valid tablename
        if ($db->tableExists($table) === false)
        {
            return false;
        }

        if (!isset($GLOBALS['loadDataContainer'][$table]))
        {
            \Controller::loadDataContainer($table);
        }

        // Delete all records of the current table that are not related to the parent table
        $arrPtable = is_array($GLOBALS['TL_DCA'][$table]['config']['buf_ptable']) ? $GLOBALS['TL_DCA'][$table]['config']['buf_ptable'] : array();

        // Traverse each field, to see if it references to a parent table
        foreach ($GLOBALS['TL_DCA'][$table]['fields'] as $field => $arrField)
        {
            if ($arrField['buf_linksTo'] != '')
            {
                // 'buf_linksTo' => 'tl_parent.id'
                if (!preg_match('/^(.+)\.(.+)$/', $arrField['buf_linksTo']))
                {
                    // Skip to next field, if foreign key isn't valid
                    continue;
                }
                list($ptable, $pfield) = explode('.', $arrField['buf_linksTo']);

                // Field must be part of a table which is declared as parent table
                if (!in_array($ptable, $arrPtable))
                {
                    // Skip to next field, if foreign key isn't valid
                    continue;
                }


                // Check for a valid tablename
                if ($db->tableExists($ptable) === false)
                {
                    continue;
                }

                // Check for a valid fieldname
                if ($db->fieldExists($pfield, $ptable) === false)
                {
                    continue;
                }

                // Delete records of the current table that are not related to the parent table
                $query = "SELECT * FROM " . $table . " WHERE NOT EXISTS (SELECT * FROM " . $ptable . " WHERE " . $ptable . "." . $pfield . " = " . $table . "." . $field . ")";
                $objStmt = $db->execute($query);
                $deletedItems = 0;
                while ($objStmt->next())
                {
                    if (intval($objStmt->{$field}) > 0)
                    {
                        $db->prepare('DELETE FROM ' . $table . ' WHERE id=?')->execute($objStmt->id);
                        $deletedItems++;
                        \System::log('Bei der Überprüfung der referentiellen Integrität ist ein Fehler aufgetreten! Der Fremdschlüssel in "' . $table . "." . $field . '" zeigt auf einen nicht vorhandenen Elterndatensatz in "' . $ptable . '". Der Kinddatensatz "' . $table . '.id=' . $objStmt->id . '" wurde aus diesem Grund gelöscht.', __METHOD__ . ' on line ' . __LINE__, TL_GENERAL);
                    }
                }
                if ($deletedItems > 0)
                {
                    $method = __FUNCTION__;
                    self::$method($table);
                    $reload = true;
                }
            }
        }


        // Delete all records of the child table that are not related to the current table
        $arrCtable = $GLOBALS['TL_DCA'][$table]['config']['buf_ctable'];

        if (is_array($arrCtable))
        {
            // Traverse each child table and delete all records of the child table that are not related to the current table
            foreach ($arrCtable as $ctable)
            {
                if (!isset($GLOBALS['loadDataContainer'][$ctable]))
                {
                    $objDC = new self;
                    $objDC->loadDataContainer($ctable);
                }

                if (!isset($GLOBALS['TL_DCA'][$ctable]['fields']))
                {
                    continue;
                }

                // Traverse each field, to see if it references to a parent table
                foreach ($GLOBALS['TL_DCA'][$ctable]['fields'] as $cfield => $arrField)
                {
                    if ($arrField['buf_linksTo'] != '')
                    {
                        // 'buf_linksTo' => 'tl_parent.id'
                        if (!preg_match('/^(.+)\.(.+)$/', $arrField['buf_linksTo']))
                        {
                            // Skip to next field, if foreign key isn't valid
                            continue;
                        }
                        list($ptable, $pfield) = explode('.', $arrField['buf_linksTo']);

                        // Check for a valid tablename
                        if ($db->tableExists($ptable) === false)
                        {
                            continue;
                        }

                        // Check for a valid fieldname
                        if ($db->fieldExists($pfield, $ptable) === false)
                        {
                            continue;
                        }

                        // Delete records of the child table that are not related to the current table
                        $query = "SELECT * FROM " . $ctable . " WHERE NOT EXISTS (SELECT * FROM " . $ptable . " WHERE " . $ptable . "." . $pfield . "=" . $ctable . "." . $cfield . ")";
                        $objStmt = $db->execute($query);
                        $deletedItems = 0;
                        while ($objStmt->next())
                        {
                            if (intval($objStmt->{$cfield}) > 0)
                            {
                                $db->prepare('DELETE FROM ' . $ctable . ' WHERE id=?')->execute($objStmt->id);
                                $deletedItems++;
                                \System::log('Bei der Überprüfung der referentiellen Integrität ist ein Fehler aufgetreten! Der Fremdschlüssel in "' . $ctable . "." . $cfield . '" zeigt auf einen nicht vorhandenen Elterndatensatz in "' . $ptable . '". Der Kinddatensatz "' . $ctable . '.id=' . $objStmt->id . '" wurde aus diesem Grund gelöscht.', __METHOD__ . ' on line ' . __LINE__, TL_GENERAL);
                            }
                        }
                        if ($deletedItems > 0)
                        {
                            $method = __FUNCTION__;
                            self::$method($ctable);
                            $reload = true;
                        }
                    }
                }
            }
        }

        // return true for a reload
        if ($reload === true)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * method called as reviseTableHook
     * -->
     * @param string $table
     * @param string $new_records
     * @param string $parent_table
     * @param string $child_tables
     * @return bool
     */
    public static function bufReviseTable($table = '', $new_records = '', $parent_table = '', $child_tables = '')
    {


        $db = \Database::getInstance();

        // delete empty comments
        $objDB = $db->prepare('SELECT * FROM tl_comment WHERE tstamp>?')->execute(0);
        while ($objDB->next())
        {
            if (trim($objDB->comment) == '')
            {
                // Delete empty comments that are older then 1 hour
                if (time() > $objDB->tstamp + 3600)
                {
                    $db->prepare('DELETE FROM tl_comment WHERE id=?')->execute($objDB->id);
                    return true;
                }
            }
        }


        // at minimum one skill must be > 0
        $objStmt = $db->execute('DELETE FROM tl_voting WHERE (skill1 + skill2 + skill3 + skill4 + skill5 + skill6 + skill7 + skill8) < 1');
        if ($objStmt->affectedRows > 0)
        {
            return true;
        }

        // set tl_member.isClassTeacher to '' if the assigned class doesn't exist
        $set = array('class' => null, 'isClassTeacher' => '');
        $objStmt = $db->prepare('UPDATE tl_member %s WHERE tl_member.class > 0 AND tl_member.class NOT IN (SELECT id FROM tl_class)')->set($set)->execute();
        if ($objStmt->affectedRows > 0)
        {
            return true;
        }

        return false;
    }

    /**
     * onload_callback for tl_member
     */
    public function onloadCallbackTlMember()
    {
        $db = \Database::getInstance();
        $set = array('class' => null, 'isClassTeacher' => '');
        // zero is not a valid value for the class field
        $db->prepare('UPDATE tl_member %s WHERE class < ? OR isClassTeacher = ?')->set($set)->execute(1, '');
    }

    /**
     * generate data records for testing
     */
    public static function generateRandomDataRecords()
    {
        $db = \Database::getInstance();
        for ($i = 0; $i < 10000; $i++)
        {
            $objMember = $db->query('SELECT id FROM tl_member ORDER BY RAND() LIMIT 1');
            $objSubject = $db->query('SELECT id FROM tl_subject ORDER BY RAND() LIMIT 1');
            $objStudent = $db->query('SELECT id FROM tl_student ORDER BY RAND() LIMIT 1');
            $set = array('teacher' => $objMember->id, 'student' => $objStudent->id, 'subject' => $objSubject->id, 'tstamp' => 999, 'skill1' => 1, 'skill2' => 2, 'skill3' => 3, 'skill4' => 4, 'skill5' => 1, 'skill6' => 1, 'skill7' => 1, 'skill8' => 1);
            $objRand = $db->prepare('SELECT id FROM tl_voting WHERE teacher=? AND student=? AND subject=?')->execute($objMember->id, $objStudent->id, $objSubject->id);
            if (!$objRand->numRows)
            {
                $db->prepare('INSERT INTO tl_voting %s')->set($set)->execute();
            }
        }
    }

    /**
     * delete random data records
     */
    public static function deleteRandomDataRecords()
    {
        $db = \Database::getInstance();
        $db->query("DELETE FROM tl_voting WHERE tstamp='999'");
    }
}
