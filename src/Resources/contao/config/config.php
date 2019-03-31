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
        'icon'   => 'bundles/markocupicbuf/images/backend/group.png',
    ),
    'student' => array(
        'tables' => array('tl_student'),
        'icon'   => 'bundles/markocupicbuf/images/backend/user.png',
    ),
    'voting'  => array(
        'tables' => array('tl_voting'),
        'icon'   => 'bundles/markocupicbuf/images/backend/star.png',
    ),
    'comment' => array(
        'tables' => array('tl_comment'),
        'icon'   => 'bundles/markocupicbuf/images/backend/comments.png',
    ),
    'subject' => array(
        'tables' => array('tl_subject'),
        'icon'   => 'bundles/markocupicbuf/images/backend/report.png',
    ),
);

// Klassenlehrer über neue Kommentare benachrichtigen
if ($_GET['adviceOnNewComments'] == 'true')
{
    $GLOBALS['TL_HOOKS']['generatePage'][] = array('Markocupic\Buf\BufHelper', 'adviceOnNewComments');
}

if (TL_MODE == 'FE')
{
    /**
     * Include the helpers
     */
    require TL_ROOT . '/system/modules/buf/helper/functions.php';

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
$GLOBALS['FE_MOD']['beurteilenfoerdern'] = array('mod_beurteilen_und_foerdern' => 'MainController');


// replace insert tags Hook
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('BufHelper', 'bufReplaceInsertTags');

// revise Table Hook
$GLOBALS['TL_HOOKS']['reviseTable'][] = array('BufHelper', 'checkForReferentialIntegrity');
$GLOBALS['TL_HOOKS']['reviseTable'][] = array('BufHelper', 'bufReviseTable');

// lang config
$GLOBALS['TL_LANG']['MSC']['newPasswordSet'] = 'Dein Passwort wurde aktualisiert.';
