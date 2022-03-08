<?php

declare(strict_types=1);

/*
 * Link Registry Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\LinkRegistryBundle\Test;

use Codefog\LinkRegistryBundle\Entry;
use Codefog\LinkRegistryBundle\Exception\InvalidEntryException;
use Codefog\LinkRegistryBundle\Exception\InvalidTypeException;
use Codefog\LinkRegistryBundle\Exception\MissingRegistryException;
use Codefog\LinkRegistryBundle\Exception\MissingRootPageException;
use Codefog\LinkRegistryBundle\LinkRegistry;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\TestCase\ContaoTestCase;
use Contao\FrontendUser;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class LinkRegistryTest extends ContaoTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    private $connection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TokenStorageInterface
     */
    private $tokenStorage;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->framework = $this->createMock(ContaoFramework::class);
        $this->tokenStorage = $this->createMock(TokenStorage::class);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['objPage']);
    }

    public function testInstantiation()
    {
        static::assertInstanceOf(LinkRegistry::class, $this->createLinkRegistryInstance([]));
    }

    public function testGetAllTypes()
    {
        $linkRegistry = $this->createLinkRegistryInstance(['foo', 'bar']);
        static::assertSame(['foo', 'bar'], $linkRegistry->getAllTypes());
    }

    public function testGetEntry()
    {
        $this->connection->method('fetchOne')->willReturn(123);
        $this->connection->method('fetchAssociative')->willReturn(['entries' => [
            'foo' => ['link' => 'http://domain.tld', 'title' => 'Foo'],
            'bar' => ['link' => '{{link_url::123}}', 'title' => ''],
            'baz' => ['link' => '{{link_url::456}}', 'title' => 'Baz'],
        ]]);

        $this->createGlobalPageObject(123);
        $linkRegistry = $this->createLinkRegistryInstance(['foo', 'bar', 'baz']);

        // External link
        $entry = $linkRegistry->getEntry('foo', 123, false);

        static::assertInstanceOf(Entry::class, $entry);
        static::assertSame('foo', $entry->getType());
        static::assertTrue($entry->hasLink());
        static::assertFalse($entry->isInternal());
        static::assertNull($entry->getPageId());
        static::assertNull($entry->getPageModel());
        static::assertEquals('http://domain.tld', $entry->getLink());
        static::assertEquals('http://domain.tld', $entry->getUrl());
        static::assertEquals('Foo', $entry->getTitle());

        // Internal link
        $pageModel = $this->mockClassWithProperties(PageModel::class);
        $pageModel->method('getFrontendUrl')->willREturn('bar.html');
        $pageModel->title = 'Bar';


        $pageModelAdapter = $this->mockAdapter(['findByPk']);
        $pageModelAdapter
            ->method('findByPk')
            ->willReturn($pageModel)
        ;

        $this->framework->method('getAdapter')->willReturn($pageModelAdapter);

        $entry = $linkRegistry->getEntry('bar', 123, false);

        static::assertInstanceOf(Entry::class, $entry);
        static::assertSame('bar', $entry->getType());
        static::assertTrue($entry->hasLink());
        static::assertTrue($entry->isInternal());
        static::assertEquals(123, $entry->getPageId());
        static::assertEquals($pageModel, $entry->getPageModel());
        static::assertEquals('{{link_url::123}}', $entry->getLink());
        static::assertEquals('bar.html', $entry->getUrl());
        static::assertEquals('Bar', $entry->getTitle());

        // Internal link when permissions fail
        $pageModel->protected = true;
        static::assertNull($linkRegistry->getEntry('baz', 123));
    }

    public function testHasEntry()
    {
        $this->connection->method('fetchOne')->willReturn(123);
        $this->connection->method('fetchAssociative')->willReturn(['entries' => [
            'foo' => ['link' => 'http://domain.tld', 'title' => ''],
            'bar' => ['link' => '', 'title' => ''],
        ]]);

        $this->createGlobalPageObject(123);
        $linkRegistry = $this->createLinkRegistryInstance(['foo', 'bar']);
        static::assertTrue($linkRegistry->hasEntry('foo', 123, false));
        static::assertFalse($linkRegistry->hasEntry('bar', 123, false));
    }

    public function testEntryTypeNotExistsError()
    {
        $this->expectException(InvalidTypeException::class);
        $linkRegistry = $this->createLinkRegistryInstance(['foo']);
        $linkRegistry->getEntry('bar');
    }

    public function testMissingGlobalPageError()
    {
        $this->expectException(MissingRootPageException::class);
        $linkRegistry = $this->createLinkRegistryInstance(['foo']);
        $linkRegistry->getEntry('foo');
    }

    public function testMissingRootPageIdError()
    {
        $this->expectException(MissingRootPageException::class);
        $this->createGlobalPageObject(0);
        $linkRegistry = $this->createLinkRegistryInstance(['foo']);
        $linkRegistry->getEntry('foo');
    }

    public function testMissingAssociatedRegistryError()
    {
        $this->connection->method('fetchOne')->willReturn(false);
        $this->expectException(MissingRegistryException::class);
        $this->createGlobalPageObject(123);
        $linkRegistry = $this->createLinkRegistryInstance(['foo']);
        $linkRegistry->getEntry('foo');
    }

    public function testMissingRegistryError()
    {
        $this->connection->method('fetchOne')->willReturn(123);
        $this->connection->method('fetchAssociative')->willReturn(false);
        $this->expectException(MissingRegistryException::class);
        $this->createGlobalPageObject(123);
        $linkRegistry = $this->createLinkRegistryInstance(['foo']);
        $linkRegistry->getEntry('foo');
    }

    public function testInvalidEntryError()
    {
        $this->connection->method('fetchOne')->willReturn(123);
        $this->connection->method('fetchAssociative')->willReturn([
            'name' => '',
            'entries' => [
                'bar' => ['link' => '', 'title' => ''],
            ],
        ]);

        $this->expectException(InvalidEntryException::class);
        $this->createGlobalPageObject(123);
        $linkRegistry = $this->createLinkRegistryInstance(['foo']);
        $linkRegistry->getEntry('foo');
    }

    public function testCheckPermissionsExternalEntry()
    {
        $linkRegistry = $this->createLinkRegistryInstance([]);
        $entry = new Entry('foo', ['link' => 'http://domain.tld', 'title' => '']);
        static::assertTrue($linkRegistry->checkPermissions($entry));
    }

    public function testCheckPermissionsInternalEntryNoPageModel()
    {
        $linkRegistry = $this->createLinkRegistryInstance([]);
        $entry = new Entry('foo', ['link' => '{{link_url::123}}', 'title' => '']);
        static::assertFalse($linkRegistry->checkPermissions($entry));
    }

    public function testCheckPermissionsInternalEntryPageNotProtected()
    {
        $linkRegistry = $this->createLinkRegistryInstance([]);

        $pageMock = $this->createMock(PageModel::class);
        $pageMock->method('__get')->willReturn(false);

        $entry = new Entry('foo', ['link' => '{{link_url::123}}', 'title' => '']);
        $entry->setPageModel($pageMock);

        static::assertTrue($linkRegistry->checkPermissions($entry));
    }

    public function testCheckPermissionsInternalEntryNoUser()
    {
        $linkRegistry = $this->createLinkRegistryInstance([]);

        $pageMock = $this->createMock(PageModel::class);
        $pageMock->method('__get')->willReturn(true);

        $this->tokenStorage->method('getToken')->willReturn(null);

        $entry = new Entry('foo', ['link' => '{{link_url::123}}', 'title' => '']);
        $entry->setPageModel($pageMock);

        static::assertFalse($linkRegistry->checkPermissions($entry));
    }

    public function testCheckPermissionsInternalEntryUserNonContao()
    {
        $linkRegistry = $this->createLinkRegistryInstance([]);

        $pageMock = $this->createMock(PageModel::class);
        $pageMock->method('__get')->willReturn(true);

        $tokenMock = $this->createMock(UsernamePasswordToken::class);
        $tokenMock->method('getUser')->willReturn(null);

        $this->tokenStorage->method('getToken')->willReturn($tokenMock);

        $entry = new Entry('foo', ['link' => '{{link_url::123}}', 'title' => '']);
        $entry->setPageModel($pageMock);

        static::assertFalse($linkRegistry->checkPermissions($entry));
    }

    public function testCheckPermissionsInternalEntryNoPageGroups()
    {
        $linkRegistry = $this->createLinkRegistryInstance([]);

        $pageMock = $this->createMock(PageModel::class);
        $pageMock->method('__get')->willReturnOnConsecutiveCalls(
            true, // protected
            [] // groups
        );

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->method('getUser')->willReturn($this->createMock(FrontendUser::class));

        $this->tokenStorage->method('getToken')->willReturn($tokenMock);

        $entry = new Entry('foo', ['link' => '{{link_url::123}}', 'title' => '']);
        $entry->setPageModel($pageMock);

        static::assertFalse($linkRegistry->checkPermissions($entry));
    }

    public function testCheckPermissionsInternalEntryInvalidUserGroups()
    {
        $linkRegistry = $this->createLinkRegistryInstance([]);

        $pageMock = $this->createMock(PageModel::class);
        $pageMock->method('__get')->willReturnOnConsecutiveCalls(
            true, // protected
            [1, 2] // groups
        );

        $frontendUserMock = $this->createMock(FrontendUser::class);
        $frontendUserMock->method('__get')->willReturn([3, 4]);

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->method('getUser')->willReturn($frontendUserMock);

        $this->tokenStorage->method('getToken')->willReturn($tokenMock);

        $entry = new Entry('foo', ['link' => '{{link_url::123}}', 'title' => '']);
        $entry->setPageModel($pageMock);

        static::assertFalse($linkRegistry->checkPermissions($entry));
    }

    public function testCheckPermissionsInternalEntry()
    {
        $linkRegistry = $this->createLinkRegistryInstance([]);

        $pageMock = $this->createMock(PageModel::class);
        $pageMock->method('__get')->willReturnOnConsecutiveCalls(
            true, // protected
            [1, 2] // groups
        );

        $frontendUserMock = $this->createMock(FrontendUser::class);
        $frontendUserMock->method('__get')->willReturn([2, 3]);

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->method('getUser')->willReturn($frontendUserMock);

        $this->tokenStorage->method('getToken')->willReturn($tokenMock);

        $entry = new Entry('foo', ['link' => '{{link_url::123}}', 'title' => '']);
        $entry->setPageModel($pageMock);

        static::assertTrue($linkRegistry->checkPermissions($entry));
    }

    private function createGlobalPageObject(int $rootId): void
    {
        $GLOBALS['objPage'] = new \stdClass();
        $GLOBALS['objPage']->rootId = $rootId;
    }

    private function createLinkRegistryInstance(array $types): LinkRegistry
    {
        return new LinkRegistry($this->connection, $this->framework, $this->tokenStorage, $types);
    }
}
