<?php

namespace DigitalVisibilityIndexTests\CrawlObserver;

use DigitalVisibilityIndex\Configuration\JSONConfiguration;
use DigitalVisibilityIndex\CrawlObserver\BusinessDirectoryLinkCrawlObserver;
use DigitalVisibilityIndex\CrawlObserver\OnlineShopCrawlObserver;
use DigitalVisibilityIndex\Storage\SimpleStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class OnlineShopCrawlObserverTest
 * @package DigitalVisibilityIndexTests\CrawlObserver
 */
class OnlineShopCrawlObserverTest extends TestCase
{
    /** @test */
    public function onlineShopIndicatorsCanBeFound()
    {
        $configuration = new JSONConfiguration(dirname(__FILE__) . '/../../config/config.json');

        $storage = new SimpleStorage();
        $observer = new OnlineShopCrawlObserver($storage, $configuration);

        $uriInterfaceStub = $this->createMock(UriInterface::class);
        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            ->willReturn('<html><head></head><body><p>Ihr Warenkorb</p></body></html>');
        $foundOnUrl = $this->createMock(UriInterface::class);

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub, $foundOnUrl);
        $observer->finishedCrawling();

        $this->assertArrayHasKey(
            'Warenkorb',
            $storage->getValue('onlineShopIndicators')
        );
    }
}
