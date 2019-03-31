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
 * Table tl_member
 */
$GLOBALS['TL_DCA']['tl_member']['config']['sql']['keys']['class'] = 'unique';
$GLOBALS['TL_DCA']['tl_member']['config']['buf_ctable'][] = 'tl_voting';
$GLOBALS['TL_DCA']['tl_member']['config']['buf_ctable'][] = 'tl_comment';
$GLOBALS['TL_DCA']['tl_member']['config']['buf_ptable'][] = 'tl_class';

$GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'][] = array('BufHelper', 'onloadCallbackTlMember');


$GLOBALS['TL_DCA']['tl_member']['fields']['isTeacher'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['isTeacher'],
    'exclude' => true,
    'search' => true,
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true, 'class' => 'clr'),
    'sql' => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['adviceOnNewComments'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['adviceOnNewComments'],
    'exclude' => true,
    'search' => true,
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => false, 'class' => 'clr'),
    'sql' => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['isClassTeacher'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['isClassTeacher'],
    'exclude' => true,
    'search' => true,
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true, 'class' => 'clr'),
    'sql' => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['class'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['class'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'select',
    'buf_linksTo' => 'tl_class.id',
    'foreignKey' => 'tl_class.name',
    'eval' => array('unique' => true, 'mandatory' => false, 'maxlength' => 255, 'includeBlankOption' => true, 'class' => 'clr'),
    'sql' => "int(10) unsigned NULL",
    'relation' => array('type' => 'belongsTo', 'load' => 'lazy')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['deviation'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['deviation'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'select',
    'options' => range(0, 4, 0.1),
    'eval' => array('rgxp' => 'alnum', 'maxlength' => 3, 'includeBlankOption' => false, 'class' => 'clr'),
    'sql' => "varchar(3) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['showCommentsNotOlderThen'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['showCommentsNotOlderThen'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'options' => range(0,37),
    'inputType' => 'select',
    'eval' => array('mandatory' => false, 'maxlength' => 255, 'includeBlankOption' => true, 'class' => 'clr'),
    'sql' => "int(2) NOT NULL",
);
$GLOBALS['TL_DCA']['tl_member']['palettes'] = array
(
    '__selector__' => array('isClassTeacher', 'login'),
    //'default'                     => '{personal_legend},firstname,lastname,dateOfBirth,gender;{address_legend:hide},company,street,postal,city,state,country;{contact_legend},phone,mobile,fax,email,website,language;{groups_legend},groups;{login_legend},login;{homedir_legend:hide},assignDir;{account_legend},disable,start,stop',
    'default' => '{personal_legend},isTeacher;firstname,lastname,gender,email;{voting_table_legend},deviation,showCommentsNotOlderThen;{class_teacher_legend},isClassTeacher;{login_legend},login;{account_legend},disable',
);

// Subpalettes
$GLOBALS['TL_DCA']['tl_member']['subpalettes'] = array
(
    'isClassTeacher' => 'class,adviceOnNewComments',
    'login' => 'username,password'
);
