<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2019 Leo Feyer
 * @package BUF (Beurteilen und Fördern)
 * @author Marko Cupic m.cupic@gmx.ch, 2014-2019
 * @link    https://github.com/markocupic/buf-bundle
 * @license MIT
 */

// Extend the default palette
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('voting_table_legend', 'personal_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addLegend('class_teacher_legend', 'personal_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField(array('isTeacher'), 'personal_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField(array('isClassTeacher'), 'class_teacher_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField(array('deviation', 'showCommentsNotOlderThen'), 'voting_table_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member');

// Selectors
$GLOBALS['TL_DCA']['tl_member']['palettes']['__selector__'][] = 'isClassTeacher';

// Palettes
$GLOBALS['TL_DCA']['tl_member']['palettes'][] = 'isClassTeacher';
$GLOBALS['TL_DCA']['tl_member']['palettes'][] = 'login';

// Subpalettes
$GLOBALS['TL_DCA']['tl_member']['subpalettes']['isClassTeacher'] = 'class,adviceOnNewComments,enableBirthdayAdvice';
$GLOBALS['TL_DCA']['tl_member']['subpalettes']['login'] = 'username,password';

// Config
$GLOBALS['TL_DCA']['tl_member']['config']['sql']['keys']['class'] = 'unique';
$GLOBALS['TL_DCA']['tl_member']['config']['buf_ctable'][] = 'tl_voting';
$GLOBALS['TL_DCA']['tl_member']['config']['buf_ctable'][] = 'tl_comment';
$GLOBALS['TL_DCA']['tl_member']['config']['buf_ptable'][] = 'tl_class';
$GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'][] = array('Markocupic\BufBundle\Helper', 'onloadCallbackTlMember');

// Fields
$GLOBALS['TL_DCA']['tl_member']['fields']['isTeacher'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_member']['isTeacher'],
    'exclude'   => true,
    'search'    => true,
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => array('submitOnChange' => true, 'class' => 'clr'),
    'sql'       => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['adviceOnNewComments'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_member']['adviceOnNewComments'],
    'exclude'   => true,
    'search'    => true,
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => array('submitOnChange' => false, 'class' => 'clr'),
    'sql'       => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['enableBirthdayAdvice'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_member']['enableBirthdayAdvice'],
    'exclude'   => true,
    'search'    => true,
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => array('submitOnChange' => false, 'class' => 'clr'),
    'sql'       => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['isClassTeacher'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_member']['isClassTeacher'],
    'exclude'   => true,
    'search'    => true,
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => array('submitOnChange' => true, 'class' => 'clr'),
    'sql'       => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['class'] = array
(
    'label'       => &$GLOBALS['TL_LANG']['tl_member']['class'],
    'exclude'     => true,
    'search'      => true,
    'sorting'     => true,
    'flag'        => 1,
    'inputType'   => 'select',
    'buf_linksTo' => 'tl_class.id',
    'foreignKey'  => 'tl_class.name',
    'eval'        => array('unique' => true, 'mandatory' => false, 'maxlength' => 255, 'includeBlankOption' => true, 'class' => 'clr'),
    'sql'         => "int(10) unsigned NULL",
    'relation'    => array('type' => 'belongsTo', 'load' => 'lazy')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['deviation'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_member']['deviation'],
    'exclude'   => true,
    'search'    => true,
    'sorting'   => true,
    'flag'      => 1,
    'inputType' => 'select',
    'options'   => range(0, 4, 0.1),
    'eval'      => array('rgxp' => 'alnum', 'maxlength' => 3, 'includeBlankOption' => false, 'class' => 'clr'),
    'sql'       => "varchar(3) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['showCommentsNotOlderThen'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_member']['showCommentsNotOlderThen'],
    'exclude'   => true,
    'search'    => true,
    'sorting'   => true,
    'flag'      => 1,
    'options'   => range(0, 37),
    'inputType' => 'select',
    'eval'      => array('mandatory' => false, 'maxlength' => 255, 'includeBlankOption' => true, 'class' => 'clr'),
    'sql'       => "int(2) NOT NULL",
);

