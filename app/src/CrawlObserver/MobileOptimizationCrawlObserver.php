<?php

namespace DigitalVisibilityIndex\CrawlObserver;

use DigitalVisibilityIndex\Storage\Storage;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;
use Symfony\Component\DomCrawler\Crawler;

class MobileOptimizationCrawlObserver extends CrawlObserver
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var bool
     */
    private $viewports;

    /**
     * @var bool
     */
    private $srcsets;

    /**
     * MobileOptimizationCrawler constructor.
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
        $this->viewports = false;
        $this->srcsets = false;
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

        $xPath = "//img[boolean(@srcset)]";
        if ($foundOnUrl == null) {
            $responsiveImages = $crawler->filterXPath($xPath)
                ->each(function (Crawler $node) {
                    return $node->attr('srcset');
                });

            if (!empty($responsiveImages)) {
                $this->srcsets = true;
            }

            $xPath = "//meta[@name='viewport']";

            $viewportMetaTag = $crawler->filterXPath($xPath)
                ->each(function (Crawler $node) {
                    return $node->attr("content");
                });
            if (!empty($viewportMetaTag)) {
                $this->viewports = true;
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
        $this->storage->setValue('viewports', $this->viewports);
        $this->storage->setValue('srcsets', $this->srcsets);
    }
}
