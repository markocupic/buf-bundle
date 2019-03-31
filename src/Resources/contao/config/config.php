<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['buf'] = array(
    // Beurteilen Und Foerdern  modules
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
/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['beurteilenfoerdern'] = array('mod_beurteilen_und_foerdern' => 'Markocupic\BufBundle\MainController');

// replace insert tags Hook
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('Markocupic\BufBundle\Helper', 'bufReplaceInsertTags');

// revise Table Hook
$GLOBALS['TL_HOOKS']['reviseTable'][] = array('Markocupic\BufBundle\Helper', 'checkForReferentialIntegrity');
$GLOBALS['TL_HOOKS']['reviseTable'][] = array('Markocupic\BufBundle\Helper', 'bufReviseTable');

// Klassenlehrer täglich per E-Mail über neue Kommentare benachrichtigen
$GLOBALS['TL_CRON']['daily']['adviceOnNewComments'] = array('Markocupic\BufBundle\Helper', 'adviceOnNewComments');

