<?php

namespace DigitalVisibilityIndexTests\CrawlObserver;

use DigitalVisibilityIndex\Configuration\Configuration;
use DigitalVisibilityIndex\CrawlObserver\LocationServiceCrawlObserver;
use DigitalVisibilityIndex\Storage\SimpleStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class LocationServiceCrawlObserverTest
 * @package DigitalVisibilityIndexTests\CrawlObserver
 */
class LocationServiceCrawlObserverTest extends TestCase
{

    private function createConfigurationMock()
    {
        $configuration = $this->createMock(Configuration::class);
        $configuration->method('get')
            ->willReturn(array("Anfahrt", "finden uns"));
        return $configuration;
    }

    public function testGoogleLocationserviceCanBeFound()
    {
        $storage = new SimpleStorage();
        $configuration = $this->createConfigurationMock();

        $observer = new LocationServiceCrawlObserver($storage, $configuration);
        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            // phpcs:ignore
            ->willReturn('<html><head></head><body><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1291.7319249377742!2d11.041979187007417!3d49.64555882548998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47a1f9d01f83241d%3A0x18d146775f335f44!2sNeuweiherstra%C3%9Fe%2C%2091083%20Baiersdorf!5e0!3m2!1sde!2sde!4v1573669992007!5m2!1sde!2sde" width="600" height="450" frameborder="0" style="border:0;" allowfullscreen=""></iframe>
        </body></html>');

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertNotEmpty($storage->getValue('locationServiceLinks'));
    }

    public function testOpenStreetMapsLocationserviceCanBeFound()
    {
        $storage = new SimpleStorage();
        $configuration = $this->createConfigurationMock();

        $observer = new LocationServiceCrawlObserver($storage, $configuration);
        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            // phpcs:ignore
            ->willReturn('<html><head></head><body><iframe width="425" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.openstreetmap.org/export/embed.html?bbox=8.66652,49.85138,8.6922,49.86637&amp;layer=mapnik" style="border: 1px solid black"></iframe><br /><small><a href="https://www.openstreetmap.org/?lat=49.858875&amp;lon=8.679359999999999&amp;zoom=16&amp;layers=M">Gr&#246;&#223;ere Karte anzeigen</a></small></body></html>');

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertNotEmpty($storage->getValue('locationServiceLinks'));
    }

    public function testKeywordMention()
    {
        $storage = new SimpleStorage();
        $configuration = $this->createConfigurationMock();

        $observer = new LocationServiceCrawlObserver($storage, $configuration);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            ->willReturn('<html><head></head><body><a>Sie finden uns hier:</a></body></html>');

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();
        $this->assertNotEmpty($storage->getValue('locationServiceKeywordMentions'));
    }

    public function testKeywordMentionisNotNoticed()
    {
        $storage = new SimpleStorage();
        $configuration = $this->createConfigurationMock();

        $observer = new LocationServiceCrawlObserver($storage, $configuration);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $html = <<<EOD
            <html>
                <head></head>
                <body>
                    <a href="#">Das hier ist ganz normaler Text ohne jegliche Hinweise auf den Standort.</a>
                </body>
            </html>
        EOD;

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            ->willReturn($html);

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();
        $this->assertEmpty($storage->getValue('locationServiceKeywordMentions'));
    }
}
