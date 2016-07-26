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
 * Register the templates
 */
TemplateLoader::addFiles([
    'be_cfg_link_registry_widget' => 'vendor/codefog/contao-link-registry/Codefog/LinkRegistryBundle/Resources/contao/templates/widgets',
]);