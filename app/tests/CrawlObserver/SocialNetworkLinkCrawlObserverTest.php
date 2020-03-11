<?php

namespace DigitalVisibilityIndexTests\CrawlObserver;

use DigitalVisibilityIndex\Configuration\JSONConfiguration;
use DigitalVisibilityIndex\CrawlObserver\SocialNetworkLinkCrawlObserver;
use DigitalVisibilityIndex\Storage\SimpleStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class SocialNetworkLinkCrawlObserverTest
 * @package DigitalVisibilityIndexTests\CrawlObserver
 */
class SocialNetworkLinkCrawlObserverTest extends TestCase
{

    public function socialLinksProvider()
    {
        return [
            ["https://www.facebook.com/fachschaftin", "facebook"],
            ["https://twitter.com/fachschaftin", "twitter"],
            ["https://www.instagram.com/siegfriedausderfachschaft", "instagram"]
        ];
    }

    /**
     * @dataProvider socialLinksProvider
     */
    public function testSocialNetworkLinksCanBeFound($link, $type)
    {
        $storage = new SimpleStorage();
        $configuration = new JSONConfiguration(dirname(__FILE__) . '/../../config/config.json');

        $observer = new SocialNetworkLinkCrawlObserver($storage, $configuration);

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
            $storage->getValue('socialNetworkLinks')[$type]
        );
    }
}
