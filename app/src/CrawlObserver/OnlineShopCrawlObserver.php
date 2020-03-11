<?php

namespace DigitalVisibilityIndex\CrawlObserver;

use DigitalVisibilityIndex\Helper\Helper;
use DigitalVisibilityIndex\Configuration\Configuration;
use DigitalVisibilityIndex\Storage\Storage;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;
use MadeITBelgium\Wappalyzer\Wappalyzer;

class OnlineShopCrawlObserver extends CrawlObserver
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
    private $onlineShopIndicators;

    /**
     * @var array
     */
    private $onlineShopTechnologies;


    /**
     * MobileOptimizationCrawler constructor.
     * @param Storage $storage
     */
    public function __construct(Storage $storage, Configuration $configuration)
    {
        $this->storage = $storage;
        $this->onlineShopIndicators = [];
        $this->onlineShopTechnologies = [];
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

        $html = Helper::tidyHtml((string)$response->getBody());
        $onlineShopKeywords = $this->configuration->get('onlineShopKeywords');

        foreach ($onlineShopKeywords as $keyword) {
            if (stripos($html, $keyword) !== false) {
                if (!array_key_exists($keyword, $this->onlineShopIndicators)) {
                    $this->onlineShopIndicators[$keyword] = $url->getHost() . $url->getPath();
                }
            }
        }

        if ($foundOnUrl == null) {
            $wappalyzer = new Wappalyzer(dirname(__FILE__) . '/../../config/apps.json');
            $result = $wappalyzer->analyze($url);
            foreach ($result["detected"] as $detectedTechnology => $value) {
                if (!in_array($detectedTechnology, $this->onlineShopTechnologies)) {
                    array_push($this->onlineShopTechnologies, $detectedTechnology);
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
        $this->storage->setValue('onlineShopIndicators', $this->onlineShopIndicators);
        $this->storage->setValue('onlineShopTechnologies', $this->onlineShopTechnologies);
    }
}
