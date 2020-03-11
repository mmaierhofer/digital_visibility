<?php

namespace DigitalVisibilityIndex\CrawlObserver;

use DigitalVisibilityIndex\Helper\Helper;
use DigitalVisibilityIndex\Configuration\Configuration;
use DigitalVisibilityIndex\Storage\Storage;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;

/**
 * Class BusinessDirectoryLinkCrawlObserver
 * @package DigitalVisibilityIndex\CrawlObserver
 */
class BusinessDirectoryLinkCrawlObserver extends CrawlObserver
{
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
    private $businessDirectoryLinks;

    /**
     * BusinessDirectoryLinkCrawlObserver constructor.
     * @param Storage $storage
     * @param Configuration $configuration
     */
    public function __construct(Storage $storage, Configuration $configuration)
    {
        $this->storage = $storage;
        $this->configuration = $configuration;
        $this->businessDirectoryLinks = [];
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
        $html = Helper::tidyHtml((string)$response->getBody());
        $businessDirectories = $this->configuration->get('businessDirectoryRegexList');

        foreach ($businessDirectories as $businessDirectory => $patterns) {
            foreach ($patterns as $pattern) {
                preg_match_all($pattern, $html, $matches);

                foreach ($matches[0] as $link) {
                    if (!array_key_exists($businessDirectory, $this->businessDirectoryLinks)) {
                        $this->businessDirectoryLinks[$businessDirectory] = [];
                    }
                    if (!in_array($link, $this->businessDirectoryLinks[$businessDirectory])) {
                        array_push($this->businessDirectoryLinks[$businessDirectory], $link);
                    }
                }
            }
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

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling()
    {
        $this->storage->setValue('businessDirectoryLinks', $this->businessDirectoryLinks);
    }
}
