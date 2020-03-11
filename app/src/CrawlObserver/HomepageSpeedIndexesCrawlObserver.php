<?php

namespace DigitalVisibilityIndex\CrawlObserver;

use DigitalVisibilityIndex\Configuration\Configuration;
use DigitalVisibilityIndex\Storage\Storage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;

/**
 * Class HomepageSpeedIndexesCrawlObserver
 * @package DigitalVisibilityIndex\CrawlObserver
 */
class HomepageSpeedIndexesCrawlObserver extends CrawlObserver
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
     * @var Client
     */
    private $lighthouseApiClient;

    /**
     * @var array
     */
    private $homepageSpeedIndexes;

    /**
     * HomepageSpeedIndexesCrawlObserver constructor.
     * @param Storage $storage
     * @param Configuration $configuration
     */
    public function __construct(Storage $storage, Configuration $configuration)
    {
        $this->storage = $storage;
        $this->configuration = $configuration;

        $this->lighthouseApiClient = new Client([
            'base_uri' => 'https://www.googleapis.com/pagespeedonline/v5/'
        ]);

        $this->homepageSpeedIndexes = [];
    }

    /**
     * @param $url
     * @param $strategy
     * @return float|null
     */
    private function getLighthouseSpeedIndex($url, $strategy = 'desktop')
    {
        try {
            $result = $this->lighthouseApiClient->request('GET', 'runPagespeed', [
                'query' => [
                    'url' => (string)$url,
                    'category' => 'performance',
                    'locale' => 'de_DE',
                    'key' => $this->configuration->get('lighthouseApiKey'),
                    'strategy' => $strategy
                ]
            ]);

            $json = json_decode($result->getBody(), true);

            return $json['lighthouseResult']['audits']['speed-index']['score'];
        } catch (GuzzleException $e) {
            print_r($e->getMessage());
            return null;
        }
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
            $this->homepageSpeedIndexes = [
                'desktop' => $this->getLighthouseSpeedIndex($url, 'desktop'),
                'mobile' => $this->getLighthouseSpeedIndex($url, 'mobile')
            ];
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
        $this->storage->setValue('homepageSpeedIndexes', $this->homepageSpeedIndexes);
    }
}
