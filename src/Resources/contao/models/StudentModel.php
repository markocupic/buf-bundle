<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2019 Leo Feyer
 * @package BUF (Beurteilen und Fördern)
 * @author Marko Cupic m.cupic@gmx.ch, 2014-2019
 * @link    https://github.com/markocupic/buf-bundle
 * @license MIT
 */

namespace Contao;

/**
 * Reads and writes students
 *
 * @package   Models
 * @author    Leo Feyer <https://github.com/leofeyer>
 * @copyright Leo Feyer 2005-2014
 */
class StudentModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_student';

    /**
     * @param $id
     * @return null|string
     */
    public static function getFullName($id)
    {
        $objDb = static::findByPk($id);
        if ($objDb !== null)
        {
            return $objDb->firstname . ' ' . $objDb->lastname;
        }
        return null;
    }

    /**
     * @param $id
     * @return mixed|null
     */
    public static function getClassnameFromStudentId($id)
    {
        $objStudent = static::findByPk($id);
        if ($objStudent !== null)
        {
            $objClass = ClassModel::findByPk($objStudent->class);
            if ($objClass !== null)
            {
                return $objClass->name;
            }
        }
        return null;
    }

}
