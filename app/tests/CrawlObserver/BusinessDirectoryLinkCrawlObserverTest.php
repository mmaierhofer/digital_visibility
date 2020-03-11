<?php

namespace DigitalVisibilityIndexTests\CrawlObserver;

use DigitalVisibilityIndex\Configuration\JSONConfiguration;
use DigitalVisibilityIndex\CrawlObserver\BusinessDirectoryLinkCrawlObserver;
use DigitalVisibilityIndex\Storage\SimpleStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class BusinessDirectoryLinkCrawlObserverTest
 * @package DigitalVisibilityIndexTests\CrawlObserver
 */
class BusinessDirectoryLinkCrawlObserverTest extends TestCase
{

    public function businessDirectoryLinksProvider()
    {
        return [
            ["https://www.yelp.de/biz/aral-roth", "yelp"],
            // phpcs:ignore
            ["https://de.foursquare.com/v/aral-tankstelle-marianne-frieser-ek-inh-cornelia-himmler/5dd9c4df69686000084ef285", "foursquare"]
        ];
    }

    /**
     * @dataProvider businessDirectoryLinksProvider
     */
    public function testBusinessDirectoryLinksCanBeFound($link, $type)
    {
        $storage = new SimpleStorage();
        $configuration = new JSONConfiguration(dirname(__FILE__) . '/../../config/config.json');

        $observer = new BusinessDirectoryLinkCrawlObserver($storage, $configuration);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);

        $html = <<<EOD
            <html>
                <head></head>
                <body>
                    <a href="{$link}">{$type}</a>
                </body>
            </html>
        EOD;

        $responseInterfaceStub->method('getBody')
            ->willReturn($html);

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertContains(
            $link,
            $storage->getValue('businessDirectoryLinks')[$type]
        );
    }
}
