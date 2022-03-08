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

use Codefog\LinkRegistryBundle\Entry;
use Codefog\LinkRegistryBundle\EventListener\InsertTagsListener;
use Codefog\LinkRegistryBundle\LinkRegistry;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';

class InsertTagsListenerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LinkRegistry
     */
    private $linkRegistry;

    /**
     * @var InsertTagsListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->linkRegistry = $this->createMock(LinkRegistry::class);
        $this->listener = new InsertTagsListener($this->linkRegistry);
    }

    public function testInstantiation()
    {
        static::assertInstanceOf(InsertTagsListener::class, $this->listener);
    }

    public function testOnReplace()
    {
        $this->linkRegistry->method('getEntry')->willReturn(new Entry('foobar', [
            'link' => 'foobar.html?foo=bar&baz=bar',
            'title' => 'Foobar',
        ]));

        static::assertFalse($this->listener->onReplace('foobar'));
        static::assertFalse($this->listener->onReplace('cfg_link_registry::foobar::foobaz'));
        static::assertSame('foobar.html?foo=bar&baz=bar', $this->listener->onReplace('cfg_link_registry::foobar::url'));
        static::assertSame('foobar.html?foo=bar&amp;baz=bar', $this->listener->onReplace('cfg_link_registry::foobar::href'));
        static::assertSame('Foobar', $this->listener->onReplace('cfg_link_registry::foobar::title'));
    }

    public function testOnReplaceException()
    {
        $this->linkRegistry->method('getEntry')->willThrowException(new \Exception());
        static::assertFalse($this->listener->onReplace('cfg_link_registry::foobar::url'));
    }
}
