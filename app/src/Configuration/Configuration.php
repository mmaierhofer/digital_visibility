<?php

namespace DigitalVisibilityIndex\Configuration;

/**
 * Interface Configuration
 * @package DigitalVisibilityIndex\Configuration
 */
interface Configuration
{
    /**
     * @param $key
     * @return mixed
     */
    public function get($key);
}
