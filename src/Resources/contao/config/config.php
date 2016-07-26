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
 * Backend modules
 */
$GLOBALS['BE_MOD']['design']['cfg_link_registry'] = [
    'tables' => ['tl_cfg_link_registry'],
    'icon'   => 'bundles/codefoglinkregistry/link-registries.png',
];

/**
 * Backend form field
 */
$GLOBALS['BE_FFL']['cfg_link_registry'] = 'Codefog\LinkRegistryBundle\LinkRegistryWidget';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = ['codefog_link_registry.insert_tags', 'replace'];