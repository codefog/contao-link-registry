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

namespace Codefog\LinkRegistryBundle;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendUser;
use Contao\PageModel;
use Doctrine\DBAL\Connection;

class LinkRegistry
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var array
     */
    private $types;

    /**
     * LinkRegistry constructor.
     *
     * @param Connection               $db
     * @param ContaoFrameworkInterface $framework
     * @param array                    $types
     */
    public function __construct(Connection $db, ContaoFrameworkInterface $framework, array $types)
    {
        $this->db        = $db;
        $this->framework = $framework;
        $this->types     = $types;
    }

    /**
     * Get the types
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Get the link
     *
     * @param string $type
     * @param int    $rootPageId
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getLink($type, $rootPageId = null)
    {
        $entry = $this->fetchEntry($type, $rootPageId);

        // Generate the URL for internal link
        if ($this->isInternalLink($entry) && ($pageModel = $this->fetchPageModel($entry)) !== null) {
            return $pageModel->getFrontendUrl();
        }

        return $entry['link'];
    }

    /**
     * Get the title
     *
     * @param string $type
     * @param int    $rootPageId
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getTitle($type, $rootPageId = null)
    {
        $entry = $this->fetchEntry($type, $rootPageId);
        $title = $entry['title'];

        // Use the page title for internal link
        if (!$title && $this->isInternalLink($entry) && ($pageModel = $this->fetchPageModel($entry)) !== null) {
            $title = $pageModel->pageTitle ?: $pageModel->title;
        }

        return $title;
    }

    /**
     * Get the link
     *
     * @param string $type
     * @param int    $rootPageId
     *
     * @return PageModel
     *
     * @throws \InvalidArgumentException
     */
    public function getPageModel($type, $rootPageId = null)
    {
        $entry = $this->fetchEntry($type, $rootPageId);

        if (!$this->isInternalLink($entry)) {
            throw new \InvalidArgumentException(sprintf('The entry "%s" is not an internal link', $type));
        }

        return $this->fetchPageModel($entry);
    }

    /**
     * Return true if the registry has entry
     *
     * @param string $type
     * @param int    $rootPageId
     * @param bool   $checkPermission
     *
     * @return bool
     */
    public function hasEntry($type, $rootPageId = null, $checkPermission = true)
    {
        try {
            $entry = $this->fetchEntry($type, $rootPageId);
        } catch (\Exception $e) {
            return false;
        }

        // Check the permission
        if ($checkPermission && !$this->checkPermission($entry)) {
            return false;
        }

        return $entry['link'] ? true : false;
    }

    /**
     * Check the permission
     *
     * @param array $entry
     *
     * @return bool
     */
    private function checkPermission(array $entry)
    {
        if (!$this->isInternalLink($entry)) {
            return true;
        }

        if (($pageModel = $this->fetchPageModel($entry)) === null) {
            return false;
        }

        $pageModel->loadDetails();

        // Check if user is logged in
        if (!FE_USER_LOGGED_IN && $pageModel->protected && !BE_USER_LOGGED_IN) {
            return false;
        }

        // Check the user groups if the page is protected
        if ($pageModel->protected && !BE_USER_LOGGED_IN) {
            $groups = $pageModel->groups; // required for empty()

            if (!is_array($groups) || empty($groups) || !count(array_intersect($groups,
                    FrontendUser::getInstance()->groups))
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Fetch the entry based on type and root page ID
     *
     * @param string $type
     * @param int    $rootPageId
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private function fetchEntry($type, $rootPageId = null)
    {
        $this->validateType($type);

        // Try to get the current root page ID
        if ($rootPageId === null) {
            $rootPageId = $GLOBALS['objPage']->rootId;

            if ($rootPageId === null) {
                throw new \InvalidArgumentException('There is no root page ID');
            }
        }

        $registry = $this->fetchRegistryByPageRootId($rootPageId);
        $entries  = deserialize($registry['entries'], true);

        if (!array_key_exists($type, $entries)) {
            throw new \InvalidArgumentException(sprintf(
                'The entry type "%s" does not exist in registry "%s"',
                $type,
                $registry['name']
            ));
        }

        return $entries[$type];
    }

    /**
     * Fetch the registry by page root ID
     *
     * @param int $rootPageId
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private function fetchRegistryByPageRootId($rootPageId)
    {
        $associatedRegistry = $this->db->fetchColumn('SELECT cfg_link_registry FROM tl_page WHERE id=?', [$rootPageId]);

        if ($associatedRegistry === false) {
            throw new \InvalidArgumentException(sprintf(
                'There is no link registry associated with root page ID %s',
                $rootPageId
            ));
        }

        $registry = $this->db->fetchAssoc('SELECT * FROM tl_cfg_link_registry WHERE id=?', [$associatedRegistry]);

        if ($registry === false) {
            throw new \InvalidArgumentException(sprintf('There is no link registry with ID %s', $associatedRegistry));
        }

        return $registry;
    }

    /**
     * Validate the type
     *
     * @param string $type
     *
     * @throws \InvalidArgumentException
     */
    private function validateType($type)
    {
        if (!in_array($type, $this->types, true)) {
            throw new \InvalidArgumentException(sprintf('The link type "%s" does not exist', $type));
        }
    }

    /**
     * Return true if the link is internal
     *
     * @param array $entry
     *
     * @return bool
     */
    private function isInternalLink(array $entry)
    {
        return $this->fetchPageId($entry) !== null;
    }

    /**
     * Fetch the page model
     *
     * @param array $entry
     *
     * @return PageModel|null
     */
    private function fetchPageModel(array $entry)
    {
        $pageId = $this->fetchPageId($entry);

        if ($pageId === null) {
            return null;
        }

        return PageModel::findPublishedById($pageId);
    }

    /**
     * Get the page ID from the link URL if it's an insert tag
     *
     * @param array $entry
     *
     * @return int|null
     */
    private function fetchPageId(array $entry)
    {
        preg_match('/{{link_url::(\d+)}}/', $entry['link'], $matches);

        if (!is_numeric($matches[1])) {
            return null;
        }

        return (int)$matches[1];
    }
}