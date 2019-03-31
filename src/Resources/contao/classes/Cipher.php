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

class Cipher
{
    protected static $strKey = 'auTzTVbNmijUhHgDCVYd';

    /**
     * @param $pure_string
     * @return string
     */
    public static function encrypt($pure_string, $key='') {
        if ($key == ''){
            $key = static::$strKey;
        }
        $encrypted_string = base64_encode(base64_encode(base64_encode($pure_string)) . $key);
        return $encrypted_string;
    }

    /**
     * @param $encrypted_string
     * @return string
     */
    public static function decrypt($encrypted_string, $key='') {
        if ($key==''){
            $key = self::$strKey;
        }
        $decrypted_string = base64_decode(base64_decode(str_replace($key,'',base64_decode($encrypted_string))));
        return $decrypted_string;
    }
}
