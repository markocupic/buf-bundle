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
 * Table tl_comment
 */
$GLOBALS['TL_DCA']['tl_comment'] = array
(

    // Config
    'config'   => array
    (
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'buf_ptable'       => array('tl_member', 'tl_student', 'tl_subject'),
        'sql'              => array
        (
            'keys' => array
            (
                'id'      => 'primary',
                'teacher' => 'index',
                'student' => 'index',
                'subject' => 'index',
            )
        )
    ),

    // List
    'list'     => array
    (
        'sorting'           => array
        (
            'mode'        => 2,
            'fields'      => array('student,teacher DESC'),
            'flag'        => 1,
            'panelLayout' => 'filter;sort,search,limit'
        ),
        'label'             => array
        (
            'fields'         => array('id', 'student', 'teacher', 'subject', 'tstamp'),
            'showColumns'    => true,
            'label_callback' => array('tl_comment', 'labelCallback')
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
                'label' => &$GLOBALS['TL_LANG']['tl_comment']['edit'],
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
                'label'      => &$GLOBALS['TL_LANG']['tl_comment']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
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
    'palettes' => array
    (
        'default' => 'published,student,subject,teacher,tstamp,dateOfCreation,comment,adviced',
    ),

    // Fields
    'fields'   => array
    (
        'id'             => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_comment']['id'],
            'sql'   => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp'         => array
        (
            'label'   => &$GLOBALS['TL_LANG']['tl_comment']['tstamp'],
            'search'  => true,
            'sorting' => true,
            'flag'    => 6,
            'eval'    => array('rgxp' => 'datim'),
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ),
        'dateOfCreation' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_comment']['dateOfCreation'],
            'search'    => true,
            'sorting'   => true,
            'default'   => time(),
            'flag'      => 6,
            'inputType' => 'text',
            'eval'      => array('rgxp' => 'date', 'datepicker' => true, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'clr wizard'),
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ),
        'student'        => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_comment']['student'],
            'exclude'          => true,
            'search'           => true,
            'sorting'          => true,
            'filter'           => true,
            'flag'             => 1,
            'inputType'        => 'select',
            'options_callback' => function () {
                $options = array();
                $objStudent = \StudentModel::findAll(array('order' => 'class,gender,lastname,firstname'));
                while ($objStudent->next())
                {
                    $options[$objStudent->id] = \ClassModel::getName($objStudent->class) . '-' . $objStudent->name . $objStudent->firstname . ' ' . $objStudent->lastname;
                }
                asort($options);
                return $options;
            },
            'buf_linksTo'      => 'tl_student.id',
            'foreignKey'       => 'tl_student.CONCAT(firstname, " " , lastname)',
            'eval'             => array('mandatory' => true, 'maxlength' => 255),
            'sql'              => "int(10) unsigned NOT NULL default '0'",
            'relation'         => array('type' => 'belongsTo', 'load' => 'eager')
        ),
        'teacher'        => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_comment']['teacher'],
            'exclude'          => true,
            'search'           => true,
            'filter'           => true,
            'sorting'          => true,
            'flag'             => 1,
            'inputType'        => 'select',
            'options_callback' => function () {
                $options = array();
                $objTeacher = \TeacherModel::findBy('isTeacher', '1');
                while ($objTeacher->next())
                {
                    $options[$objTeacher->id] = $objTeacher->firstname . ' ' . $objTeacher->lastname;
                }
                asort($options);
                return $options;
            },
            'buf_linksTo'      => 'tl_member.id',
            'foreignKey'       => 'tl_member.CONCAT(firstname, " " , lastname)',
            'eval'             => array('mandatory' => true, 'maxlength' => 255),
            'sql'              => "int(10) unsigned NOT NULL default '0'",
            'relation'         => array('type' => 'belongsTo', 'load' => 'eager')
        ),
        'subject'        => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_comment']['subject'],
            'exclude'          => true,
            'search'           => true,
            'sorting'          => true,
            'filter'           => true,
            'flag'             => 1,
            'inputType'        => 'select',
            'options_callback' => function () {
                $options = array();
                $objSubject = \SubjectModel::findAll();
                while ($objSubject->next())
                {
                    $options[$objSubject->id] = $objSubject->name . ' (' . $objSubject->acronym . ')';
                }
                asort($options);
                return $options;
            },
            'buf_linksTo'      => 'tl_subject.id',
            'foreignKey'       => 'tl_subject.name',
            'eval'             => array('mandatory' => true, 'maxlength' => 255),
            'sql'              => "int(10) unsigned NOT NULL default '0'",
            'relation'         => array('type' => 'belongsTo', 'load' => 'eager')
        ),
        'comment'        => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_comment']['comment'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'textarea',
            'eval'      => array('mandatory' => false),
            'sql'       => "mediumtext NULL"
        ),
        'adviced'        => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_comment']['adviced'],
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => array('submitOnChange' => false, 'class' => 'clr'),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'published'      => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_comment']['published'],
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => array('submitOnChange' => false, 'class' => 'clr'),
            'sql'       => "char(1) NOT NULL default ''"
        )
    )
);

/**
 * Class tl_comment
 */
class tl_comment extends Backend
{

    /**
     * Add an image to each record
     * @param array $row
     * @param string $label
     * @param DataContainer $dc
     * @param array $args
     *
     * @return array
     */
    public function labelCallback($row, $label, DataContainer $dc, $args)
    {
        $args[1] = '(' . \StudentModel::getClassnameFromStudentId($args[1]) . ') ' . \StudentModel::getFullName($args[1]);
        $args[2] = \StringUtil::substr(\TeacherModel::findByPk($args[2])->firstname, 1, '') . '. ' . \TeacherModel::findByPk($args[2])->lastname;
        $args[3] = \SubjectModel::findByPk($args[3])->acronym;

        return $args;
    }

}


