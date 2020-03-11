<?php

namespace DigitalVisibilityIndexTests\CrawlObserver;

use DigitalVisibilityIndex\Configuration\Configuration;
use DigitalVisibilityIndex\CrawlObserver\HomepageScreenshotsCrawlObserver;
use DigitalVisibilityIndex\Storage\SimpleStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Browsershot\Browsershot;
use Mockery;

/**
 * Class HomepageScreenshotsCrawlObserverTest
 * @package DigitalVisibilityIndexTests\CrawlObserver
 */
class HomepageScreenshotsCrawlObserverTest extends TestCase
{
    /**
     * @return UriInterface
     */
    private function getUriInterfaceMock($path)
    {
        $uriInterfaceMock = $this->createMock(UriInterface::class);
        $uriInterfaceMock->method('getScheme')
            ->willReturn('https');
        $uriInterfaceMock->method('getHost')
            ->willReturn('www.ein-kleiner-laden.de');
        $uriInterfaceMock->method('getPath')
            ->willReturn($path);

        return $uriInterfaceMock;
    }

    /**
     * @return Browsershot
     */
    private function getBrowsershotMock()
    {
        $browsershot = Mockery::mock('overload:Spatie\Browsershot\Browsershot');
        $browsershot->shouldReceive('noSandbox')
            ->twice()
            ->withNoArgs()
            ->andReturnSelf();
        $browsershot->shouldReceive('windowSize')
            ->once()
            ->withArgs([1920, 1080])
            ->andReturnSelf();
        $browsershot->shouldReceive('setDelay')
            ->twice()
            ->withArgs([2000])
            ->andReturnSelf();
        $browsershot->shouldReceive('save')
            ->twice()
            ->withAnyArgs()
            ->andReturnSelf();
        $browsershot->shouldReceive('device')
            ->once()
            ->withArgs(["iPhone X"])
            ->andReturnSelf();

        return $browsershot;
    }

    public function testScreenshotsMethodHasBeenTriggered()
    {
        $storage = new SimpleStorage();

        $this->getBrowsershotMock();

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->method('get')
            ->willReturn('/foo/');

        $observer = new HomepageScreenshotsCrawlObserver($storage, $configurationMock);

        $uriInterfaceMock = $this->getUriInterfaceMock('/');
        $responseInterfaceMock = $this->createMock(ResponseInterface::class);

        $observer->crawled($uriInterfaceMock, $responseInterfaceMock, null);
        $observer->finishedCrawling();

        $this->assertContains(
            'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855_desktop.png',
            $storage->getValue('screenshots')
        );

        $this->assertContains(
            'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855_mobile.png',
            $storage->getValue('screenshots')
        );
    }

    public function testScreenshotsMethodHasNotBeenTriggered()
    {
        $storage = new SimpleStorage();

        $this->getBrowsershotMock();

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->method('get')
                 ->willReturn('/foo/');

        $observer = new HomepageScreenshotsCrawlObserver($storage, $configurationMock);

        $uriInterfaceMock = $this->getUriInterfaceMock('/eine-unterseite/');
        $foundUriMock = $this->getUriInterfaceMock('/');
        $responseInterfaceMock = $this->createMock(ResponseInterface::class);

        $observer->crawled($uriInterfaceMock, $responseInterfaceMock, $foundUriMock);
        $observer->finishedCrawling();

        $this->assertEmpty($storage->getValue('screenshots'));
    }
}
