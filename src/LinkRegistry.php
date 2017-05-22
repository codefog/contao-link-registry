<?php

declare(strict_types=1);

/*
 * Link Registry Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\LinkRegistryBundle;

use Codefog\LinkRegistryBundle\Exception\InvalidEntryException;
use Codefog\LinkRegistryBundle\Exception\InvalidTypeException;
use Codefog\LinkRegistryBundle\Exception\MissingRegistryException;
use Codefog\LinkRegistryBundle\Exception\MissingRootPageException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendUser;
use Contao\PageModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LinkRegistry
{
    /**
     * Cache.
     *
     * @var array
     */
    private $cache = [];

    /**
     * Database connection.
     *
     * @var Connection
     */
    private $db;

    /**
     * Contao framework.
     *
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Types.
     *
     * @var array
     */
    private $types;

    /**
     * LinkRegistry constructor.
     *
     * @param Connection               $db
     * @param ContaoFrameworkInterface $framework
     * @param TokenStorageInterface    $tokenStorage
     * @param array                    $types
     */
    public function __construct(
        Connection $db,
        ContaoFrameworkInterface $framework,
        TokenStorageInterface $tokenStorage,
        array $types
    ) {
        $this->db = $db;
        $this->framework = $framework;
        $this->tokenStorage = $tokenStorage;
        $this->types = $types;
    }

    /**
     * Get the entry.
     *
     * @param string   $type
     * @param int|null $rootPageId
     * @param bool     $checkPermissions
     *
     * @return Entry|null
     */
    public function getEntry(string $type, int $rootPageId = null, bool $checkPermissions = true): ?Entry
    {
        $cacheKey = 'entry_'.$type.'_'.$rootPageId;

        if (!array_key_exists($cacheKey, $this->cache)) {
            $this->cache[$cacheKey] = $this->createEntry($type, $rootPageId);
        }

        /** @var Entry $entry */
        $entry = $this->cache[$cacheKey];

        // Return null if the entry has no link
        if (!$entry->hasLink()) {
            return null;
        }

        // Return null if the permissions are insufficient
        if ($checkPermissions && !$this->checkPermissions($entry)) {
            return null;
        }

        return $entry;
    }

    /**
     * Return true if the registry has entry.
     *
     * @param string   $type
     * @param int|null $rootPageId
     * @param bool     $checkPermissions
     *
     * @return bool
     */
    public function hasEntry(string $type, int $rootPageId = null, bool $checkPermissions = true): bool
    {
        return $this->getEntry($type, $rootPageId, $checkPermissions) !== null;
    }

    /**
     * Check the permissions.
     *
     * @param Entry $entry
     *
     * @return bool
     */
    public function checkPermissions(Entry $entry): bool
    {
        if (!$entry->isInternal()) {
            return true;
        }

        if (($pageModel = $entry->getPageModel()) === null) {
            return false;
        }

        $pageModel->loadDetails();

        // Return true for the unprotected pages
        if (!$pageModel->protected) {
            return true;
        }

        // Return false if there is no user logged in
        if (($token = $this->tokenStorage->getToken()) === null) {
            return false;
        }

        $user = $token->getUser();

        // Return false if the user is not coming from Contao
        if (!$user instanceof FrontendUser) {
            return false;
        }

        $groups = $pageModel->groups;

        // Return false if the user has no access to the page
        if (!is_array($groups) || empty($groups) || count(array_intersect($groups, $user->groups)) < 1) {
            return false;
        }

        return true;
    }

    /**
     * Get all registered types.
     *
     * @return array
     */
    public function getAllTypes(): array
    {
        return $this->types;
    }

    /**
     * Create the entry object.
     *
     * @param string   $type
     * @param int|null $rootPageId
     *
     * @return Entry
     */
    private function createEntry(string $type, int $rootPageId = null): Entry
    {
        $entry = new Entry($type, $this->fetchEntryData($type, $rootPageId));

        /** @var PageModel $adapter */
        $adapter = $this->framework->getAdapter(PageModel::class);

        // Set the page model for the entry
        if ($entry->isInternal() && ($pageModel = $adapter->findByPk($entry->getPageId())) !== null) {
            $entry->setPageModel($pageModel);
        }

        return $entry;
    }

    /**
     * Fetch the entry based on type and root page ID.
     *
     * @param string $type
     * @param int    $rootPageId
     *
     * @throws InvalidEntryException
     * @throws InvalidTypeException
     * @throws MissingRootPageException
     *
     * @return array
     */
    private function fetchEntryData(string $type, int $rootPageId = null): array
    {
        if (!in_array($type, $this->types, true)) {
            throw new InvalidTypeException(sprintf('The entry type "%s" does not exist', $type));
        }

        // Try to get the current root page ID
        if ($rootPageId === null) {
            if (!isset($GLOBALS['objPage'])) {
                throw new MissingRootPageException('There is no global page object');
            }

            $rootPageId = $GLOBALS['objPage']->rootId ?: null;

            if ($rootPageId === null) {
                throw new MissingRootPageException('There is no root page ID');
            }
        }

        $registry = $this->fetchRegistryData((int) $rootPageId);
        $entries = $registry['entries'];

        if (!array_key_exists($type, $entries)) {
            throw new InvalidEntryException(sprintf('The entry type "%s" does not exist in registry "%s"', $type, $registry['name']));
        }

        return $entries[$type];
    }

    /**
     * Fetch the registry data by page root ID.
     *
     * @param int $rootPageId
     *
     * @throws MissingRegistryException
     *
     * @return array
     */
    private function fetchRegistryData(int $rootPageId): array
    {
        $cacheKey = 'registry_'.$rootPageId;

        if (!array_key_exists($cacheKey, $this->cache)) {
            $associatedRegistry = $this->db->fetchColumn('SELECT cfg_link_registry FROM tl_page WHERE id=?', [$rootPageId]);

            if ($associatedRegistry === false) {
                throw new MissingRegistryException(sprintf('There is no link registry associated with root page ID %s', $rootPageId));
            }

            $registry = $this->db->fetchAssoc('SELECT * FROM tl_cfg_link_registry WHERE id=?', [$associatedRegistry]);

            if ($registry === false) {
                throw new MissingRegistryException(sprintf('There is no link registry with ID %s', $associatedRegistry));
            }

            $registry['entries'] = StringUtil::deserialize($registry['entries'], true);
            $this->cache[$cacheKey] = $registry;
        }

        return $this->cache[$cacheKey];
    }
}
