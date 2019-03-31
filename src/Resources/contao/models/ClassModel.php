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
 * Class ClassModel
 * @package Contao
 */
class ClassModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_class';

    /**
     * @param $id
     * @return null|string
     */
    public static function getName($id)
    {
        $objDb = static::findByPk($id);
        if ($objDb !== null)
        {
            return $objDb->name;
        }
        return null;
    }
}
