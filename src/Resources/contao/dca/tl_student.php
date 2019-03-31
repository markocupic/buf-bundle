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
 * Table tl_member
 */
$GLOBALS['TL_DCA']['tl_student'] = array
(

    // Config
    'config'      => array
    (
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'buf_ptable'       => array('tl_class'),
        'buf_ctable'       => array('tl_voting', 'tl_comment'),

        'sql' => array
        (
            'keys' => array
            (
                'id'    => 'primary',
                'class' => 'index'
            )
        )
    ),

    // List
    'list'        => array
    (
        'sorting'           => array
        (
            'mode'        => 2,
            'fields'      => array('gender,lastname,firstname DESC'),
            'flag'        => 1,
            'panelLayout' => 'filter;sort,search,limit'
        ),
        'label'             => array
        (
            'fields'      => array('id', 'lastname', 'firstname', 'class'),
            'showColumns' => true,
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations'        => array
        (
            'edit'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_member']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ),
            'copy'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_member']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ),
            'delete' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_member']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'toggle' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_member']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => array('tl_student', 'toggleIcon')
            ),
            'show'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_member']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes'    => array
    (
        //'__selector__'                => array('login', 'assignDir'),
        'default' => '{personal_legend},gender,firstname,lastname,dateOfBirth,class',
    ),

    // Subpalettes
    'subpalettes' => array
    ( //'login'                       => 'username,password',
      //'assignDir'                   => 'homeDir'
    ),

    // Fields
    'fields'      => array
    (
        'id'          => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_student']['id'],
            'sql'   => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp'      => array
        (
            'search'  => true,
            'sorting' => true,
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ),
        'disable'     => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_student']['disable'],
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => array('submitOnChange' => true, 'class' => 'clr'),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'gender'      => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_student']['gender'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'inputType' => 'select',
            'options'   => array('male', 'female'),
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval'      => array('mandatory' => true, 'includeBlankOption' => false, 'tl_class' => 'clr'),
            'sql'       => "varchar(32) NOT NULL default ''"
        ),
        'dateOfBirth' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_student']['dateOfBirth'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array('rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'),
            'sql'       => "varchar(11) NOT NULL default ''"
        ),
        'lastname'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_student']['lastname'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => array('mandatory' => true, 'maxlength' => 255),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'firstname'   => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_student']['firstname'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => array('mandatory' => true, 'maxlength' => 255),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'class'       => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_student']['class'],
            'exclude'          => true,
            'search'           => true,
            'sorting'          => true,
            'flag'             => 1,
            'inputType'        => 'select',
            'options_callback' => function () {
                $options = array();
                $objClass = \ClassModel::findAll();
                while ($objClass->next())
                {
                    $options[$objClass->id] = $objClass->name;
                }
                return $options;
            },
            'buf_linksTo'      => 'tl_class.id',
            'foreignKey'       => 'tl_class.name',
            'eval'             => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr wizard'),
            'sql'              => "int(10) unsigned NOT NULL default '0'",
            'relation'         => array('type' => 'belongsTo', 'load' => 'eager')
        )
    )

);

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_student extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(Input::get('tid')))
        {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . $row['disable'];

        if ($row['disable'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['disable'] ? 0 : 1) . '"') . '</a> ';
    }

    /**
     * Disable/enable a user group
     *
     * @param integer $intId
     * @param boolean $blnVisible
     * @param DataContainer $dc
     */
    public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        $objVersions = new Versions('tl_student', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_student']['fields']['disable']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_student']['fields']['disable']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, ($dc ?: $this));
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, ($dc ?: $this));
                }
            }
        }

        $time = time();

        // Update the database
        $this->Database->prepare("UPDATE tl_student SET tstamp=$time, disable='" . ($blnVisible ? '' : 1) . "' WHERE id=?")
            ->execute($intId);

        $objVersions->create();
    }
}

