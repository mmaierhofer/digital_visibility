<?php

namespace DigitalVisibilityIndex\Configuration;

/**
 * Class JSONConfiguration
 * @package DigitalVisibilityIndex\Configuration
 */
class JSONConfiguration implements Configuration
{
    private $configuration;

    /**
     * JSONConfiguration constructor.
     */
    public function __construct($fileName)
    {
        $this->configuration = json_decode(file_get_contents($fileName), true);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        return (array_key_exists($key, $this->configuration))
            ? $this->configuration[$key]
            : null;
    }
}
