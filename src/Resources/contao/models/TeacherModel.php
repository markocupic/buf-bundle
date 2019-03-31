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
 * Class TeacherModel
 * @package Contao
 */
class TeacherModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_member';

    /**
     * @return mixed
     */
    public static function isClassTeacher()
    {
        $objUser = \System::importStatic('FrontendUser');
        if ($objUser->isClassTeacher)
        {
            if ($objUser->class > 0)
            {
                return $objUser->class;
            }
        }
        return false;
    }

    /**
     * @return mixed|null
     */
    public static function getOwnClass()
    {
        if (static::isClassTeacher())
        {
            return static::isClassTeacher();
        }
        return null;
    }

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

}
