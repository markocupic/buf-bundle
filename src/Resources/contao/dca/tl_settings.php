<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2019 Leo Feyer
 * @package BUF (Beurteilen und Fördern)
 * @author Marko Cupic m.cupic@gmx.ch, 2014-2019
 * @link    https://github.com/markocupic/buf-bundle
 * @license MIT
 */

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{beurteilen_und_foerdern_legend},buf_name_school,buf_encode_params';

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['buf_name_school'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['buf_name_school'],
    'inputType' => 'text',
    'default'   => '',
    'eval'      => array('tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['buf_encode_params'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['buf_encode_params'],
    'inputType' => 'checkbox',
    'eval'      => array('tl_class' => '')
);
