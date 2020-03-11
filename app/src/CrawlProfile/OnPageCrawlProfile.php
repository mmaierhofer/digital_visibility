<?php

namespace DigitalVisibilityIndex\CrawlProfile;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlProfile;

/**
 * Class OnPageCrawlProfile
 * @package DigitalVisibilityIndex\CrawlProfile
 */
class OnPageCrawlProfile extends CrawlProfile
{
    /**
     * @var UriInterface
     */
    protected $baseUrl;

    /**
     * OnPageCrawlProfile constructor.
     * @param UriInterface|string $baseUrl
     */
    public function __construct($baseUrl)
    {
        if (!$baseUrl instanceof UriInterface) {
            $baseUrl = new Uri($baseUrl);
        }

        $this->baseUrl = $baseUrl;
    }

    /**
     * @param UriInterface $url
     * @return bool
     */
    public function shouldCrawl(UriInterface $url): bool
    {
        if ($this->baseUrl->getHost() !== $url->getHost()) {
            return false;
        }

        $headers = get_headers($url, 1);

        if (!isset($headers['Content-Type'])) {
            return false;
        }

        $contentType = is_array($headers['Content-Type'])
            ? implode(";", $headers['Content-Type'])
            : $headers['Content-Type'];

        return $contentType
            && strpos($contentType, 'text/html') !== false;
    }
}
