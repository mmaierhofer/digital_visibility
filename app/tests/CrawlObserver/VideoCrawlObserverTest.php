<?php

namespace DigitalVisibilityIndexTests\CrawlObserver;

use DigitalVisibilityIndex\CrawlObserver\VideoCrawlObserver;
use DigitalVisibilityIndex\Storage\SimpleStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class VideoCrawlObserverTest
 * @package DigitalVisibilityIndexTests\CrawlObserver
 */
class VideoCrawlObserverTest extends TestCase
{
    public function testPlainVideoCanBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new VideoCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            ->willReturn('<html><head></head><body><video src="media.mp4"></video></body></html>');

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertContains(
            'media.mp4',
            $storage->getValue('videoLinks')
        );
    }

    public function testYoutubeVideoCanBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new VideoCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);

        $html = <<<EOD
            <html>
                <head></head>
                <body>
                    <iframe width="560" height="315" 
                        src="https://www.youtube.com/embed/4XpnKHJAok8" frameborder="0" 
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                    </iframe>
                </body>
            </html>
        EOD;

        $responseInterfaceStub->method('getBody')
            ->willReturn($html);

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertContains(
            'https://www.youtube.com/embed/4XpnKHJAok8',
            $storage->getValue('videoLinks')
        );
    }

    public function testVimeoVideoCanBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new VideoCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);

        $html = <<<EOD
            <html>
                <head></head>
                <body>
                    <iframe src="https://player.vimeo.com/video/220643078?color=C8E3E2&portrait=0&badge=0" width="640" 
                        height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen>
                    </iframe>
                    <p><a href="https://vimeo.com/220643078">The Ultimate Running Machine</a> 
                        from <a href="https://vimeo.com/voyagertv">Voyager</a> on 
                        <a href="https://vimeo.com">Vimeo</a>.</p>
                </body>
            </html>
        EOD;

        $responseInterfaceStub->method('getBody')
            ->willReturn($html);

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertContains(
            'https://player.vimeo.com/video/220643078?color=C8E3E2&portrait=0&badge=0',
            $storage->getValue('videoLinks')
        );
    }
}
