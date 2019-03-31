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
 * Class EditClasslistController
 *
 * Front end module buf
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class EditClasslistController extends \Frontend
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
        // insert new students
        if (\Input::post('TL_FORM') && $_POST['insert_lastname'])
        {
            foreach ($_POST['insert_lastname'] as $k => $v)
            {
                if (!\Validator::isAlphabetic($_POST['insert_lastname'][$k]) || !\Validator::isAlphabetic($_POST['insert_firstname'][$k]))
                {
                    continue;
                }
                if ($_POST['insert_dateOfBirth'][$k] == '' || false === strtotime($_POST['insert_dateOfBirth'][$k]))
                {
                    $dateOfBirth = '';
                }
                else
                {
                    $dateOfBirth = strtotime($_POST['insert_dateOfBirth'][$k]);
                }

                $set = array(
                    'tstamp'      => time(),
                    'lastname'    => $_POST['insert_lastname'][$k],
                    'firstname'   => $_POST['insert_firstname'][$k],
                    'gender'      => $_POST['insert_gender'][$k],
                    'dateOfBirth' => $dateOfBirth,
                    'class'       => \TeacherModel::getOwnClass(),
                    'disable'     => ''
                );
                \Database::getInstance()->prepare('INSERT INTO tl_student %s')->set($set)->execute();
            }
            $this->reload();
        }

        $objTemplate->action = $this->generateFrontendUrl($objPage->row(), '/do/edit_classlist');

        $objTemplate->User = $this->User;

        if ($this->User->class)
        {
            $objStudent = $this->Database->prepare('SELECT * FROM tl_student WHERE class=? ORDER BY gender DESC, lastname, firstname')->execute($this->User->class);
            $objTemplate->students = $objStudent->fetchAllAssoc();
        }
        return $objTemplate;
    }

}
