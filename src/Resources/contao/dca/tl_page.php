<?php

/*
 * Link Registry Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

/**
 * Extend the palettes.
 */
\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('cfg_link_registry_legend', 'title_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
    ->addField('cfg_link_registry', 'cfg_link_registry_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page');

/*
 * Add the fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['cfg_link_registry'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_page']['cfg_link_registry'],
    'exclude' => true,
    'flag' => 1,
    'inputType' => 'select',
    'foreignKey' => 'tl_cfg_link_registry.name',
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
];
