<?php

declare(strict_types=1);

/*
 * Link Registry Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\LinkRegistryBundle\EventListener;

use Codefog\LinkRegistryBundle\LinkRegistry;
use Doctrine\DBAL\Connection;

class DataContainerListener
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var LinkRegistry
     */
    private $linkRegistry;

    /**
     * LinkRegistryContainer constructor.
     *
     * @param Connection   $db
     * @param LinkRegistry $linkRegistry
     */
    public function __construct(Connection $db, LinkRegistry $linkRegistry)
    {
        $this->db = $db;
        $this->linkRegistry = $linkRegistry;
    }

    /**
     * Generate the label.
     *
     * @param array $data
     *
     * @return string
     */
    public function generateLabel(array $data): string
    {
        $type = $GLOBALS['TL_DCA']['tl_cfg_link_registry_entry']['fields']['type']['reference'][$data['type']];
        $page = $this->db->fetchAssoc('SELECT id, title FROM tl_page WHERE id=?', [$data['page']]);

        return $type.' <span style="padding-left:3px;color:#b3b3b3;">['.$page['name'].', ID: '.$page['id'].']</span>';
    }

    /**
     * Get the available link types.
     *
     * @return array
     */
    public function getLinkTypes(): array
    {
        return $this->linkRegistry->getAllTypes();
    }
}
