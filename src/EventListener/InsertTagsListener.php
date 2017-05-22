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
use Contao\StringUtil;

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
     * On replace the insert tags.
     *
     * @param string $tag
     *
     * @return string|bool
     */
    public function onReplace(string $tag)
    {
        $chunks = StringUtil::trimsplit('::', $tag);

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
     * Parse the insert tag.
     *
     * @param string $type
     * @param string $param
     *
     * @throws \InvalidArgumentException
     *
     * @return string|bool
     */
    private function parse(string $type, string $param)
    {
        switch ($param) {
            case 'url':
                return $this->linkRegistry->getEntry($type)->getUrl();
            case 'href':
                return ampersand($this->linkRegistry->getEntry($type)->getUrl());
            case 'title':
                return $this->linkRegistry->getEntry($type)->getTitle();
        }

        return false;
    }
}
