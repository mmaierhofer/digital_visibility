<?php

namespace DigitalVisibilityIndex\Storage;

/**
 * Class SimpleStorage
 * @package DigitalVisibilityIndex\Storage
 */
class SimpleStorage implements Storage
{
    /**
     * @var array
     */
    private $storage;

    /**
     * SimpleStorage constructor.
     */
    public function __construct()
    {
        $this->storage = [];
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getValue($key)
    {
        return isset($this->storage[$key])
            ? $this->storage[$key]
            : null;
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function setValue($key, $value)
    {
        if ($value === null) {
            unset($this->storage[$key]);
        } else {
            $this->storage[$key] = $value;
        }
    }
}
