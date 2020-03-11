<?php

namespace DigitalVisibilityIndex;

use Symfony\Component\Console\Application;

/**
 * Class ConsoleApplication
 * @package DigitalVisibilityIndex
 */
class ConsoleApplication extends Application
{
    /**
     * ConsoleApplication constructor.
     */
    public function __construct()
    {
        parent::__construct('Digital Visibility Index', '0.1');
        $this->add(new CrawlCommand());
    }
}
