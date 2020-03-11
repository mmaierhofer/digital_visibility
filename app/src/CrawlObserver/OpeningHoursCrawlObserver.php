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
 * Class OpeningHoursCrawlObserver
 * @package DigitalVisibilityIndex\CrawlObserver
 */
class OpeningHoursCrawlObserver extends CrawlObserver
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
    private $openingHoursInformation;

    /**
     * OpeningHoursCrawlObserver constructor.
     * @param Storage $storage
     * @param Configuration $configuration
     */
    public function __construct(Storage $storage, Configuration $configuration)
    {
        $this->storage = $storage;
        $this->configuration = $configuration;
        $this->openingHoursInformation = [];
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
        $words = $this->configuration->get('openingHoursKeywords');

        foreach ($words as $word) {
            if (strpos($html, $word) !== false) {
                $path = $url->getHost() . $url->getPath();
                if (!in_array($path, $this->openingHoursInformation)) {
                    array_push($this->openingHoursInformation, $path);
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
        $this->storage->setValue('openingHours', $this->openingHoursInformation);
    }
}
