<?php

declare(strict_types=1);

/*
 * Link Registry Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\LinkRegistryBundle\Test\DependencyInjection;

use Codefog\LinkRegistryBundle\DependencyInjection\LinkRegistryExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LinkRegistryExtensionTest extends TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder();
        $extension = new LinkRegistryExtension();
        $extension->load([], $container);

        $this->assertTrue($container->hasDefinition('cfg_link_registry'));
        $this->assertTrue($container->hasDefinition('cfg_link_registry.listener.data_container'));
        $this->assertTrue($container->hasDefinition('cfg_link_registry.listener.insert_tags'));
        $this->assertTrue($container->hasParameter('cfg_link_registry.types'));
    }
}
