<?php

namespace DigitalVisibilityIndexTests\CrawlObserver;

use DigitalVisibilityIndex\CrawlObserver\EmailAddressesCrawlObserver;
use DigitalVisibilityIndex\Storage\SimpleStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class EmailAddressesCrawlObserverTest
 * @package DigitalVisibilityIndexTests\CrawlObserver
 */
class EmailAddressesCrawlObserverTest extends TestCase
{
    public function testEmailAddressInPlainTextCanBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new EmailAddressesCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            ->willReturn('<html><head></head><body>max.mustermann@test.de</body></html>');

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertContains(
            'max.mustermann@test.de',
            $storage->getValue('emailAddresses')
        );
    }

    public function testEmailAddressInHtmlCommentCanNotBeFound()
    {
        $storage = new SimpleStorage();
        $observer = new EmailAddressesCrawlObserver($storage);

        $uriInterfaceStub = $this->createMock(UriInterface::class);

        $responseInterfaceStub = $this->createMock(ResponseInterface::class);
        $responseInterfaceStub->method('getBody')
            ->willReturn('<html><head></head><body><div>test</div><!-- max.mustermann@test.de --></body></html>');

        $observer->crawled($uriInterfaceStub, $responseInterfaceStub);
        $observer->finishedCrawling();

        $this->assertEmpty($storage->getValue('emailAddresses'));
    }
}
