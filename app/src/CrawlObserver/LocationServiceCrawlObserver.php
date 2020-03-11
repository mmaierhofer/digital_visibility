<?php

namespace DigitalVisibilityIndex\CrawlObserver;

use DigitalVisibilityIndex\Storage\Storage;
use DigitalVisibilityIndex\Configuration\Configuration;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class LocationServiceCrawlObserver
 * @package DigitalVisibilityIndex\CrawlObserver
 */
class LocationServiceCrawlObserver extends CrawlObserver
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var array
     */
    private $locationServiceLinks;

    /**
     * @var array
     */
    private $locationServiceKeywordMentions = false;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * LocationServiceCrawlObserver constructor.
     * @param Storage $storage
     */
    public function __construct(Storage $storage, Configuration $configuration)
    {
        $this->storage = $storage;
        $this->locationServiceLinks = [];
        $this->locationServiceKeywordMentions = [];
        $this->configuration = $configuration;
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
        $crawler = new Crawler((string)$response->getBody());

        $keywords = $this->configuration->get('locationServiceKeywords');
        $xPathKeyword = "//text()[false";
        foreach ($keywords as $keyword) {
            $xPathKeyword = $xPathKeyword . " or contains(.,'$keyword')";
        }
        $xPathKeyword = $xPathKeyword . "]";
        $locationServiceKeywordMentions = $crawler->filterXPath($xPathKeyword)
            ->each(function () {
                return true;
            });
        if ($locationServiceKeywordMentions) {
            array_push($this->locationServiceKeywordMentions, (string) $url);
        }

        $xPath = "//iframe[contains(@src, 'www.google.com/maps/') or contains(@src, 'www.openstreetmap.org')]";
        $locationServiceLinks = $crawler->filterXPath($xPath)
            ->each(function (Crawler $node) {
                return $node->attr("src");
            });
        foreach ($locationServiceLinks as $link) {
            if (!in_array($link, $this->locationServiceLinks)) {
                array_push($this->locationServiceLinks, $link);
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
        $this->storage->setValue('locationServiceLinks', $this->locationServiceLinks);
        $this->storage->setValue('locationServiceKeywordMentions', $this->locationServiceKeywordMentions);
    }
}
