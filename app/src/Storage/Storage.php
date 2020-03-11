<?php

namespace DigitalVisibilityIndex\Storage;

/**
 * Interface Storage
 * @package DigitalVisibilityIndex\Storage
 */
interface Storage
{
    /**
     * @param $key
     * @return mixed
     */
    public function getValue($key);

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setValue($key, $value);
}
