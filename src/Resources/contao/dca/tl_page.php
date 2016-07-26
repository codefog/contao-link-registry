<?php

/**
 * link-registry extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2016 Codefog
 *
 * @author  Codefog <http://codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

/**
 * Extend the palettes
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['root'] .= ';{cfg_link_registry_legend:hide},cfg_link_registry';

/**
 * Add the fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['cfg_link_registry'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_page']['cfg_link_registry'],
    'exclude'    => true,
    'inputType'  => 'select',
    'foreignKey' => 'tl_cfg_link_registry.name',
    'eval'       => ['includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql'        => "int(10) unsigned NOT NULL default '0'",
];