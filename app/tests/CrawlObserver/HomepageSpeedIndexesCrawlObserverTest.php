<?php

namespace DigitalVisibilityIndexTests\CrawlObserver;

use DigitalVisibilityIndex\Configuration\Configuration;
use DigitalVisibilityIndex\CrawlObserver\HomepageSpeedIndexesCrawlObserver;
use DigitalVisibilityIndex\Storage\SimpleStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Mockery;

/**
 * Class HomepageSpeedIndexesCrawlObserverTest
 * @package DigitalVisibilityIndexTests\CrawlObserver
 */
class HomepageSpeedIndexesCrawlObserverTest extends TestCase
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
            ->willReturn('www.foo.bar');
        $uriInterfaceMock->method('getPath')
            ->willReturn($path);

        return $uriInterfaceMock;
    }

    /**
     * @return Configuration
     */
    private function getConfigurationMock()
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->method('get')
            ->willReturn('api-key');

        return $configurationMock;
    }

    /**
     * @return Client
     */
    private function getGuzzleClientMock()
    {
        $sampleResult = [
            'lighthouseResult' => [
                'audits' => [
                    'speed-index' => [
                        'score' => 0.99
                    ]
                ]
            ]
        ];

        $guzzleClient = Mockery::mock('overload:GuzzleHttp\Client');
        $guzzleClient->shouldReceive('request')
            ->twice()
            ->withAnyArgs()
            ->andReturnSelf();
        $guzzleClient->shouldReceive('getBody')
            ->twice()
            ->withAnyArgs()
            ->andReturn(json_encode($sampleResult));

        return $guzzleClient;
    }

    public function testHomepageSpeedIndexesHasBeenRequested()
    {
        $storage = new SimpleStorage();

        $this->getGuzzleClientMock();

        $configurationMock = $this->getConfigurationMock();

        $observer = new HomepageSpeedIndexesCrawlObserver($storage, $configurationMock);

        $uriInterfaceMock = $this->getUriInterfaceMock('/');
        $responseInterfaceMock = $this->createMock(ResponseInterface::class);

        $observer->crawled($uriInterfaceMock, $responseInterfaceMock, null);
        $observer->finishedCrawling();

        $this->assertArrayHasKey('desktop', $storage->getValue('homepageSpeedIndexes'));
        $this->assertEquals(0.99, $storage->getValue('homepageSpeedIndexes')['desktop']);

        $this->assertArrayHasKey('mobile', $storage->getValue('homepageSpeedIndexes'));
        $this->assertEquals(0.99, $storage->getValue('homepageSpeedIndexes')['mobile']);
    }

    public function testHomepageSpeedIndexesHasBeenNotRequested()
    {
        $storage = new SimpleStorage();

        $this->getGuzzleClientMock();

        $configurationMock = $this->getConfigurationMock();

        $observer = new HomepageSpeedIndexesCrawlObserver($storage, $configurationMock);

        $uriInterfaceMock = $this->getUriInterfaceMock('/foo');
        $foundUriInterfaceMock = $this->getUriInterfaceMock('/');
        $responseInterfaceMock = $this->createMock(ResponseInterface::class);

        $observer->crawled($uriInterfaceMock, $responseInterfaceMock, $foundUriInterfaceMock);
        $observer->finishedCrawling();

        $this->assertEmpty($storage->getValue('homepageSpeedIndexes'));
    }
}
