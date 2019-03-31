<?php
/**
 * Created by PhpStorm.
 * User: Marko
 * Date: 31.03.2019
 * Time: 10:36
 */

namespace Markocupic\BufBundle\Helper;

class Helper
{
    public static function setQueryString($arrItems, $encode = false)
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
}