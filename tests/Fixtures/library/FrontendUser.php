<?php

namespace Contao\Fixtures;

class FrontendUser extends \Contao\User
{
    public $authenticated = true;

    public $data = [];

    /** @noinspection MagicMethodsValidityInspection */
    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }


    public static function getInstance()
    {
        return new self();
    }

    public function authenticate()
    {
        return $this->authenticated;
    }

    public function setUserFromDb()
    {
        // ignore
    }
}
