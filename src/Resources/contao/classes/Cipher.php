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
 * Class Cipher
 * @package Markocupic\BufBundle
 */
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
