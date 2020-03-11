<?php

namespace DigitalVisibilityIndexTests\CrawlObserver;

use DigitalVisibilityIndex\Configuration\JSONConfiguration;
use DigitalVisibilityIndex\CrawlObserver\OpeningHoursCrawlObserver;
use DigitalVisibilityIndex\Storage\SimpleStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class OpeningHoursCrawlObserverTest
 * @package DigitalVisibilityIndexTests\CrawlObserver
 */
class OpeningHoursCrawlObserverTest extends TestCase
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

    public function openingHoursProvider()
    {
        return [
            ["Mo-Fr 09:00-20:00"],
            ["Montag bis Freitag von 9 Uhr bis 10 Uhr"]
        ];
    }

    /**
     * @dataProvider openingHoursProvider
     */
    public function testOpeningHourInformationCanBeFound($time)
    {
        $storage = new SimpleStorage();
        $configuration = new JSONConfiguration(dirname(__FILE__) . '/../../config/config.json');

        $observer = new OpeningHoursCrawlObserver($storage, $configuration);

        $uriInterfaceStub = $this->getUriInterfaceMock('/eine-unterseite/');

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);

        $foundUriMock = $this->getUriInterfaceMock('/');

        $html = <<<EOD
            <html>
                <head></head>
                <body>
                    {$time}
                </body>
            </html>
        EOD;

        $responseInterfaceStub->method('getBody')
            ->willReturn($html);

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub, $foundUriMock);
        $observer->finishedCrawling();

        $path = $uriInterfaceStub->getHost() . $uriInterfaceStub->getPath();
        $this->assertContains(
            $path,
            $storage->getValue('openingHours')
        );
    }
}
