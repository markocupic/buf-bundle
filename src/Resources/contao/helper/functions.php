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
 * @param $arrItems
 * @param bool $encode
 * @return mixed|string
 */
function setQueryString($arrItems, $encode = false)
{
    $encode = true;
    $blnEncode = false;
    if (!is_array($arrItems) || !count($arrItems)) {
        return '';
    }
    ksort($arrItems);

    $queryStr = '?';

    foreach ($arrItems as $k => $v) {
        if ($v == '') {
            continue;
        }
        $queryStr .= '&' . $k . '=' . $v;

    }

    $queryStr = str_replace('?&', '?', $queryStr);
    $queryStr = ampersand($queryStr, $blnEncode);

    // encode query 
    if ($GLOBALS['TL_CONFIG']['buf_encode_params']) {
        $queryStr = str_replace('?', '', $queryStr);
        $enc = \Cipher::encrypt($queryStr);
        $queryStr = '?vars=' . $enc;
    }
    return $queryStr;
}