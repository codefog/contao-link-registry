<?php

/*
 * Link Registry Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

/**
 * Backend modules.
 */
$GLOBALS['BE_MOD']['design']['cfg_link_registry'] = [
    'tables' => ['tl_cfg_link_registry'],
];

/*
 * Backend form field
 */
$GLOBALS['BE_FFL']['cfg_link_registry'] = 'Codefog\LinkRegistryBundle\Widget\LinkRegistryWidget';

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = ['cfg_link_registry.insert_tags', 'onReplace'];
