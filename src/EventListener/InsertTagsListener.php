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

namespace Codefog\LinkRegistryBundle\EventListener;

use Codefog\LinkRegistryBundle\LinkRegistry;

class InsertTagsListener
{
    /**
     * @var LinkRegistry
     */
    private $linkRegistry;

    /**
     * Hooks constructor.
     *
     * @param LinkRegistry $linkRegistry
     */
    public function __construct(LinkRegistry $linkRegistry)
    {
        $this->linkRegistry = $linkRegistry;
    }

    /**
     * Replace the insert tags
     *
     * @param string $tag
     *
     * @return string|bool
     */
    public function replace($tag)
    {
        $chunks = trimsplit('::', $tag);

        if ($chunks[0] === 'cfg_link_registry') {
            try {
                $value = $this->parse($chunks[1], $chunks[2]);
            } catch (\Exception $e) {
                return false;
            }

            return $value;
        }

        return false;
    }

    /**
     * Parse the insert tag
     *
     * @param string $type
     * @param string $param
     *
     * @return string|bool
     *
     * @throws \InvalidArgumentException
     */
    private function parse($type, $param)
    {
        switch ($param) {
            case 'link':
            case 'url':
                return $this->linkRegistry->getLink($type);

            case 'href':
                return ampersand($this->linkRegistry->getLink($type));

            case 'title':
                return $this->linkRegistry->getTitle($type);
                break;
        }

        return false;
    }
}