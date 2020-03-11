<?php

namespace DigitalVisibilityIndexTests\CrawlObserver;

use DigitalVisibilityIndex\CrawlObserver\TelephoneNumberCrawlObserver;
use DigitalVisibilityIndex\Storage\SimpleStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class TelephoneNumberCrawlObserverTest
 * @package DigitalVisibilityIndexTests\CrawlObserver
 */
class TelephoneNumberCrawlObserverTest extends TestCase
{
    public function testGermanPhoneNumberFormatVersionOneCanBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new TelephoneNumberCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            ->willReturn('<html><head></head><body>90763 Foo Bar 0911 / 979079310 Bar Foo</body></html>');

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertContains(
            '+49911979079310',
            $storage->getValue('telephoneNumbers')
        );
    }

    public function testGermanPhoneNumberFormatVersionTwoCanBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new TelephoneNumberCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            ->willReturn('<html><head></head><body>90763 Foo Bar (0911) 979079311 Bar Foo</body></html>');

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertContains(
            '+49911979079311',
            $storage->getValue('telephoneNumbers')
        );
    }

    public function testGermanPhoneNumberFormatVersionThreeCanBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new TelephoneNumberCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            ->willReturn('<html><head></head><body>90763 Foo Bar +49(0)911979079377 Bar Foo</body></html>');

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertContains(
            '+49911979079377',
            $storage->getValue('telephoneNumbers')
        );
    }

    public function testGermanPhoneNumberFormatVersionFourCanBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new TelephoneNumberCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            ->willReturn('<html><head></head><body>90763 Foo Bar 0911-9377793 Bar Foo</body></html>');

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertContains(
            '+499119377793',
            $storage->getValue('telephoneNumbers')
        );
    }

    public function testGermanPhoneNumberFormatVersionFiveCanBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new TelephoneNumberCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            ->willReturn('<html><head></head><body>90763 Foo Bar 06434 90 72 77 Bar Foo</body></html>');

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertContains(
            '+496434907277',
            $storage->getValue('telephoneNumbers')
        );
    }
}
