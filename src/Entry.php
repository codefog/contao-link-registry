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

use Contao\PageModel;

class Entry
{
    /**
     * @var string
     */
    private $link;

    /**
     * @var int|null
     */
    private $pageId;

    /**
     * @var PageModel|null
     */
    private $pageModel;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $url;

    /**
     * Entry constructor.
     *
     * @param string $type
     * @param array  $data
     */
    public function __construct(string $type, array $data)
    {
        $this->link = $data['link'];
        $this->pageId = $this->extractPageId($data);
        $this->title = $data['title'];
        $this->type = $type;
    }

    /**
     * Return true if the link is internal.
     *
     * @return bool
     */
    public function isInternal(): bool
    {
        return $this->pageId !== null;
    }

    /**
     * Get the link.
     *
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * Return true if the entry has link.
     *
     * @return bool
     */
    public function hasLink(): bool
    {
        return $this->link ? true : false;
    }

    /**
     * Get the page ID.
     *
     * @return int|null
     */
    public function getPageId(): ?int
    {
        return $this->pageId;
    }

    /**
     * Get the page model.
     *
     * @return PageModel|null
     */
    public function getPageModel()
    {
        return $this->pageModel;
    }

    /**
     * Set the page model.
     *
     * @param PageModel $pageModel
     */
    public function setPageModel(PageModel $pageModel): void
    {
        $this->pageModel = $pageModel;
    }

    /**
     * Get the title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        if (!$this->title && $this->pageModel !== null) {
            $this->title = $this->pageModel->pageTitle ?: $this->pageModel->title;
        }

        return $this->title;
    }

    /**
     * Get the type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the URL.
     *
     * @return string
     */
    public function getUrl(): string
    {
        if ($this->url === null) {
            if ($this->isInternal() && $this->pageModel !== null) {
                $this->url = $this->pageModel->getFrontendUrl();
            } else {
                $this->url = $this->link;
            }
        }

        return $this->url;
    }

    /**
     * Extract the page ID from the link URL if it's an insert tag.
     *
     * @param array $data
     *
     * @return int|null
     */
    private function extractPageId(array $data): ?int
    {
        preg_match('/{{link_url::(\d+)}}/', $data['link'], $matches);

        if (!isset($matches[1]) || !is_numeric($matches[1])) {
            return null;
        }

        return (int) $matches[1];
    }
}
