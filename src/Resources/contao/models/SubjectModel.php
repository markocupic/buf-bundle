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
namespace Contao;

/**
 * Reads and writes subject
 *
 * @package   Models
 * @author    Leo Feyer <https://github.com/leofeyer>
 * @copyright Leo Feyer 2005-2014
 */
class SubjectModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_subject';

    /**
     * @param $id
     * @return null|string
     */
    public static function getName($id)
    {
        $objDb = static::findByPk($id);
        if ($objDb !== null) {
            return $objDb->name;
        }
        return null;
    }


}
