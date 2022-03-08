<?php

namespace Contao\Fixtures;

use Contao\CoreBundle\Framework\Adapter;

class PageModel extends Adapter
{
    private $data;

    public function __get($key)
    {
        return $this->data[$key];
    }

    public function loadDetails()
    {
    }

    public function getFrontendUrl()
    {
        return 'bar.html';
    }

    public static function findByPk()
    {
        return new self(__CLASS__);
    }
}
