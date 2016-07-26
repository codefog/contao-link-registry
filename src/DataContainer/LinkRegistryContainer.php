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

namespace Codefog\LinkRegistryBundle\DataContainer;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

class LinkRegistryContainer
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
     * LinkRegistryContainer constructor.
     *
     * @param Connection               $db
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(Connection $db, ContaoFrameworkInterface $framework)
    {
        $this->db        = $db;
        $this->framework = $framework;
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

        return $type . ' <span style="padding-left:3px;color:#b3b3b3;">[' . $page['name'] . ', ID: ' . $page['id'] . ']</span>';
    }

    /**
     * Get the available types
     *
     * @return array
     */
    public function getTypes()
    {
        $types = [];

        // @todo

        return $types;
    }

    /**
     * Return the page picker wizard
     *
     * @param DataContainer $dc
     *
     * @return string
     */
    public function generatePagePicker(DataContainer $dc)
    {
        /**
         * @var \Contao\Image $imageAdapter
         * @var \Contao\Input $inputAdapter
         */
        $imageAdapter = $this->framework->getAdapter('Contao\Image');
        $inputAdapter = $this->framework->getAdapter('Contao\Input');

        return ' <a href="contao/page.php?do=' . $inputAdapter->get('do') . '&amp;table=' . $dc->table . '&amp;field=' . $dc->field . '&amp;value=' . str_replace(['{{link_url::', '}}'], '', $dc->value) . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']) . '" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", $GLOBALS['TL_LANG']['MOD']['page'][0])) . '\',\'url\':this.href,\'id\':\'' . $dc->field . '\',\'tag\':\'ctrl_'. $dc->field . (($inputAdapter->get('act') === 'editAll') ? '_' . $dc->id : '') . '\',\'self\':this});return false">' . $imageAdapter->getHtml('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top;cursor:pointer"') . '</a>';
    }
}