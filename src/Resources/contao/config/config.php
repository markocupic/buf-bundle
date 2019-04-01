<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2019 Leo Feyer
 * @package BUF (Beurteilen und Fördern)
 * @author Marko Cupic m.cupic@gmx.ch, 2014-2019
 * @link    https://github.com/markocupic/buf-bundle
 * @license MIT
 */

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['buf'] = array(
    'class'   => array(
        'tables' => array('tl_class'),
    ),
    'student' => array(
        'tables' => array('tl_student'),
    ),
    'voting'  => array(
        'tables' => array('tl_voting'),
    ),
    'comment' => array(
        'tables' => array('tl_comment'),
    ),
    'subject' => array(
        'tables' => array('tl_subject'),
    ),
);

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['beurteilenfoerdern'] = array('mod_beurteilen_und_foerdern' => 'Markocupic\BufBundle\MainController');

if (TL_MODE == 'FE')
{
    // Javascript
    $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/markocupicbuf/jquery/main.js';
    $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/markocupicbuf/jquery/editClasslist.js';
    $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/markocupicbuf/jquery/votingTable.js';
    $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/markocupicbuf/jquery/averageTable.js';
    $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/markocupicbuf/jquery/tallySheet.js';

    // Datepicker
    $GLOBALS['TL_CSS'][] = 'bundles/markocupicbuf/bootstrap-datepicker/dist/css/bootstrap-datepicker.standalone.css';
    $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/markocupicbuf/bootstrap-datepicker/dist/js/bootstrap-datepicker.js';
}

// Replace insert tags Hook
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('Markocupic\BufBundle\Helper', 'bufReplaceInsertTags');

// Revise Table Hook
$GLOBALS['TL_HOOKS']['reviseTable'][] = array('Markocupic\BufBundle\Helper', 'checkForReferentialIntegrity');
$GLOBALS['TL_HOOKS']['reviseTable'][] = array('Markocupic\BufBundle\Helper', 'bufReviseTable');

// Cron: Klassenlehrer täglich per E-Mail über neue Kommentare benachrichtigen
$GLOBALS['TL_CRON']['daily']['notifyOnNewComments'] = array('Markocupic\BufBundle\CommentsNotify', 'notifyOnNewComments');
$GLOBALS['TL_CRON']['daily']['birthdayNotify'] = array('Markocupic\BufBundle\BirthdayNotify', 'birthdayNotify');

