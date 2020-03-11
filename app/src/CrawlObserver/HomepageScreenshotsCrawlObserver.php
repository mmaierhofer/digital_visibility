<?php

namespace DigitalVisibilityIndex\CrawlObserver;

use DigitalVisibilityIndex\Configuration\Configuration;
use DigitalVisibilityIndex\Storage\Storage;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Browsershot\Browsershot;
use Spatie\Crawler\CrawlObserver;

/**
 * Class HomepageScreenshotsCrawlObserver
 * @package DigitalVisibilityIndex\CrawlObserver
 */
class HomepageScreenshotsCrawlObserver extends CrawlObserver
{
    const BROWSER_SCREENSHOT_DELAY = 2000;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var array
     */
    private $screenshots;

    /**
     * HomepageScreenshotsCrawlObserver constructor.
     * @param Storage $storage
     * @param Configuration $configuration
     */
    public function __construct(Storage $storage, Configuration $configuration)
    {
        $this->storage = $storage;
        $this->configuration = $configuration;
        $this->screenshots = [];
    }

    /**
     * @param UriInterface $url
     * @param ResponseInterface $response
     * @param UriInterface|null $foundOnUrl
     */
    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null
    ) {
        if ($foundOnUrl == null) {
            $urlHash = hash('sha256', $url);

            // Desktop Screenshot
            $desktopScreenshotFileName = $urlHash . '_desktop.png';
            $desktopScreenshotFilePath = dirname(__FILE__)
                . $this->configuration->get('screenshotsAssetsPath')
                . $desktopScreenshotFileName;

            (new Browsershot($url))
                ->noSandbox()
                ->windowSize(1920, 1080)
                ->setDelay(self::BROWSER_SCREENSHOT_DELAY)
                ->save($desktopScreenshotFilePath);

            array_push($this->screenshots, $desktopScreenshotFileName);

            // Mobile Screenshot
            $mobileScreenshotFileName = $urlHash . '_mobile.png';
            $mobileScreenshotFilePath = dirname(__FILE__)
                . $this->configuration->get('screenshotsAssetsPath')
                . $mobileScreenshotFileName;

            (new Browsershot($url, true))
                ->noSandbox()
                ->device('iPhone X')
                ->setDelay(self::BROWSER_SCREENSHOT_DELAY)
                ->save($mobileScreenshotFilePath);

            array_push($this->screenshots, $mobileScreenshotFileName);
        }
    }

    /**
     * @param UriInterface $url
     * @param RequestException $requestException
     * @param UriInterface|null $foundOnUrl
     */
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null
    ) {
    }

    public function finishedCrawling()
    {
        $this->storage->setValue('screenshots', $this->screenshots);
    }
}
