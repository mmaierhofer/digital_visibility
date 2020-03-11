<?php

namespace DigitalVisibilityIndex\CrawlObserver;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Spatie\Crawler\CrawlObserver;

/**
 * Class LoggingCrawlObserver
 * @package DigitalVisibilityIndex\CrawlObserver
 */
class LoggingCrawlObserver extends CrawlObserver
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * LoggingCrawlObserver constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Called when the crawler will crawl the url.
     *
     * @param UriInterface $url
     */
    public function willCrawl(UriInterface $url)
    {
        $this->logger->info("willCrawl: {$url}");
    }

    /**
     * Called when the crawler has crawled the given url successfully.
     *
     * @param UriInterface $url
     * @param ResponseInterface $response
     * @param UriInterface|null $foundOnUrl
     */
    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null
    ) {
        $logMessage = (string)$foundOnUrl
            ? "hasBeenCrawled: {$url} - found on {$foundOnUrl}"
            : "hasBeenCrawled: {$url}";

        $this->logger->info($logMessage);
    }

    /**
     * Called when the crawler had a problem crawling the given url.
     *
     * @param UriInterface $url
     * @param RequestException $requestException
     * @param UriInterface|null $foundOnUrl
     */
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null
    ) {
        $logMessage = (string)$foundOnUrl
            ? "crawlFailed: {$url} - found on {$foundOnUrl} - exception: {$requestException->getMessage()}"
            : "crawlFailed: {$url} - exception: {$requestException->getMessage()}";

        $this->logger->error($logMessage);
    }

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling()
    {
        $this->logger->info("finishedCrawling");
    }
}
