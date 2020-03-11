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
 * Class SocialNetworkLinkCrawlObserver
 * @package DigitalVisibilityIndex\CrawlObserver
 */
class SocialNetworkLinkCrawlObserver extends CrawlObserver
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
    private $socialNetworkLinks;

    /**
     * SocialNetworkLinkCrawlObserver constructor.
     * @param Storage $storage
     * @param Configuration $configuration
     */
    public function __construct(Storage $storage, Configuration $configuration)
    {
        $this->storage = $storage;
        $this->configuration = $configuration;
        $this->socialNetworkLinks = [];
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
        $socialNetworks = $this->configuration->get('socialMediaRegexList');

        foreach ($socialNetworks as $socialNetwork => $patterns) {
            foreach ($patterns as $pattern) {
                preg_match_all($pattern, $html, $matches);

                foreach ($matches[0] as $link) {
                    if (!array_key_exists($socialNetwork, $this->socialNetworkLinks)) {
                        $this->socialNetworkLinks[$socialNetwork] = [];
                    }
                    if (!in_array($link, $this->socialNetworkLinks[$socialNetwork])) {
                        array_push($this->socialNetworkLinks[$socialNetwork], $link);
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
        $this->storage->setValue('socialNetworkLinks', $this->socialNetworkLinks);
    }
}
