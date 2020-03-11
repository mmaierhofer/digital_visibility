<?php

namespace DigitalVisibilityIndex\CrawlObserver;

use DigitalVisibilityIndex\Storage\Storage;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class VideoCrawlObserver
 * @package DigitalVisibilityIndex\CrawlObserver
 */
class VideoCrawlObserver extends CrawlObserver
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var array
     */
    private $videoLinks;

    /**
     * VideoCrawlObserver constructor.
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
        $this->videoLinks = [];
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

        $xPath = "//video[boolean(@src)] | "
            . "//video/source[boolean(@src)] | "
            . "//iframe["
            . "contains(@src, 'youtube.com/embed') or "
            . "contains(@src, 'player.vimeo.com')"
            . "]";

        $videoLinks = $crawler->filterXPath($xPath)
            ->each(function (Crawler $node) {
                return $node->attr("src");
            });

        foreach ($videoLinks as $link) {
            if (!in_array($link, $this->videoLinks)) {
                array_push($this->videoLinks, $link);
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
        $this->storage->setValue('videoLinks', $this->videoLinks);
    }
}
