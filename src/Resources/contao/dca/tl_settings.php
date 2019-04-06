<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2019 Leo Feyer
 * @package BUF (Beurteilen und Fördern)
 * @author Marko Cupic m.cupic@gmx.ch, 2014-2019
 * @link https://github.com/markocupic/buf-bundle
 * @license MIT
 */

// Extend the default palette
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('beurteilen_und_foerdern_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField(array('buf_name_school', 'buf_encode_params'), 'beurteilen_und_foerdern_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_settings');

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
