<?php

declare(strict_types=1);

/*
 * Link Registry Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\LinkRegistryBundle\Test\EventListener;

use Codefog\LinkRegistryBundle\EventListener\DataContainerListener;
use Codefog\LinkRegistryBundle\LinkRegistry;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class DataContainerListenerTest extends TestCase
{
    /**
     * @var DataContainerListener
     */
    private $listener;

    protected function setUp(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn(['name' => 'Foobar', 'id' => 123]);

        $linkRegistry = $this->createMock(LinkRegistry::class);
        $linkRegistry->method('getAllTypes')->willReturn(['foo', 'bar']);

        $this->listener = new DataContainerListener($connection, $linkRegistry);
    }

    public function testInstantiation()
    {
        static::assertInstanceOf(DataContainerListener::class, $this->listener);
    }

    public function testGenerateLabel()
    {
        $GLOBALS['TL_DCA']['tl_cfg_link_registry_entry']['fields']['type']['reference'] = [
            'foobar' => '',
        ];

        static::assertNotEmpty($this->listener->generateLabel([
            'type' => 'foobar',
            'page' => 123,
        ]));
    }

    public function testGetLinkTypes()
    {
        static::assertEquals(['foo', 'bar'], $this->listener->getLinkTypes());
    }
}
