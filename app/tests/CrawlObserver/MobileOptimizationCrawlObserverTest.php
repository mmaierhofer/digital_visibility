<?php

namespace DigitalVisibilityIndexTests\CrawlObserver;

use DigitalVisibilityIndex\CrawlObserver\MobileOptimizationCrawlObserver;
use DigitalVisibilityIndex\Storage\SimpleStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class MobileOptimizationCrawlObserverTest
 * @package DigitalVisibilityIndexTests\CrawlObserver
 */
class MobileOptimizationCrawlObserverTest extends TestCase
{
    public function testViewportInHeaderCanBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new MobileOptimizationCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);

        $html = <<<EOD
            <html><head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="Free Web tutorials">
            </head><body></body></html>
        EOD;


        $responseInterfaceStub->method('getBody')
            ->willReturn($html);

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertEquals(
            true,
            $storage->getValue('viewports')
        );
    }

    public function testViewportInHeaderCanNotBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new MobileOptimizationCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);

        $html = <<<EOD
            <html><head>
            <meta name="description" content="Free Web tutorials">
            </head><body></body></html>
        EOD;


        $responseInterfaceStub->method('getBody')
            ->willReturn($html);

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertEmpty($storage->getValue('viewports'));
    }

    public function testSrcSetInBodyCanBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new MobileOptimizationCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);

        $html = <<<EOD
                        <html><head></head><body>
                        <img src="needles-1440.jpg" srcset="needles-780.jpg  780w">
                        </body></html>
        EOD;


        $responseInterfaceStub->method('getBody')
            ->willReturn($html);

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertEquals(
            true,
            $storage->getValue('srcsets')
        );
    }

    public function testSrcSetInBodyCanNotBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new MobileOptimizationCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);

        $html = <<<EOD
                    <body>
                        <img src="bricks2_normal.jpg">
                    </body>
        EOD;


        $responseInterfaceStub->method('getBody')
            ->willReturn($html);

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertEmpty($storage->getValue('srcsets'));
    }
}
