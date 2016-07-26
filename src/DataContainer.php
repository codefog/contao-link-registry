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
use Doctrine\DBAL\Connection;

class DataContainer
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
     * @var LinkRegistry
     */
    private $linkRegistry;

    /**
     * LinkRegistryContainer constructor.
     *
     * @param Connection               $db
     * @param ContaoFrameworkInterface $framework
     * @param LinkRegistry             $linkRegistry
     */
    public function __construct(Connection $db, ContaoFrameworkInterface $framework, LinkRegistry $linkRegistry)
    {
        $this->db           = $db;
        $this->framework    = $framework;
        $this->linkRegistry = $linkRegistry;
    }

    /**
     * Generate the label
     *
     * @param array $data
     *
     * @return string
     */
    public function generateLabel(array $data)
    {
        $type = $GLOBALS['TL_DCA']['tl_cfg_link_registry_entry']['fields']['type']['reference'][$data['type']];
        $page = $this->db->fetchAssoc("SELECT id, title FROM tl_page WHERE id=?", [$data['page']]);

        return $type.' <span style="padding-left:3px;color:#b3b3b3;">['.$page['name'].', ID: '.$page['id'].']</span>';
    }

    /**
     * Get the available link types
     *
     * @return array
     */
    public function getLinkTypes()
    {
        return $this->linkRegistry->getTypes();
    }
}