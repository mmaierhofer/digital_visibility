<?php

namespace DigitalVisibilityIndex\CrawlObserver;

use DigitalVisibilityIndex\Helper\Helper;
use DigitalVisibilityIndex\Storage\Storage;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;

/**
 * Class EmailAddressesCrawlObserver
 * @package DigitalVisibilityIndex\CrawlObserver
 */
class EmailAddressesCrawlObserver extends CrawlObserver
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var array
     */
    private $emailAddresses;

    /**
     * EmailAddressesCrawlObserver constructor.
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
        $this->emailAddresses = [];
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

        // Pattern based on RFC5322 by https://emailregex.com/
        // phpcs:ignore
        $pattern = '/(?:[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/i';
        preg_match_all($pattern, $html, $matches);

        foreach ($matches[0] as $emailAddress) {
            if (!in_array($emailAddress, $this->emailAddresses)) {
                array_push($this->emailAddresses, $emailAddress);
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
        $this->storage->setValue('emailAddresses', $this->emailAddresses);
    }
}
