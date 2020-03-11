<?php

namespace DigitalVisibilityIndexTests\CrawlObserver;

use DigitalVisibilityIndex\CrawlObserver\LoggingCrawlObserver;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

/**
 * Class LoggingCrawlObserverTest
 * @package DigitalVisibilityIndexTests\CrawlObserver
 */
class LoggingCrawlObserverTest extends TestCase
{
    /**
     * @param $object
     * @param $attributeName
     * @param $value
     * @throws \ReflectionException
     */
    protected function setObjectAttribute($object, $attributeName, $value)
    {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($attributeName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    public function testWillCrawlLogging()
    {
        $url = "http://www.foo.bar";

        $uriInterfaceMock = $this->createMock(UriInterface::class);
        $uriInterfaceMock->method('__toString')
            ->willReturn($url);

        $loggerInterfaceMock = $this->createMock(LoggerInterface::class);
        $loggerInterfaceMock->expects($this->once())
            ->method('info')
            ->with("willCrawl: {$url}");

        $crawlObserver = new LoggingCrawlObserver($loggerInterfaceMock);
        $crawlObserver->willCrawl($uriInterfaceMock);
    }

    public function testCrawledLoggingWithoutFoundOnUrl()
    {
        $url = "http://www.foo.bar";

        $uriInterfaceMock = $this->createMock(UriInterface::class);
        $uriInterfaceMock->method('__toString')
            ->willReturn($url);

        $loggerInterfaceMock = $this->createMock(LoggerInterface::class);
        $loggerInterfaceMock->expects($this->once())
            ->method('info')
            ->with("hasBeenCrawled: {$url}");

        $responseInterfaceMock = $this->createMock(ResponseInterface::class);

        $crawlObserver = new LoggingCrawlObserver($loggerInterfaceMock);
        $crawlObserver->crawled($uriInterfaceMock, $responseInterfaceMock);
    }

    public function testCrawledLoggingWithFoundOnUrl()
    {
        $url = "http://www.foo.bar/bar.html";
        $foundOnUrl = "http://www.foo.bar";

        $uriInterfaceMock = $this->createMock(UriInterface::class);
        $uriInterfaceMock->method('__toString')
            ->willReturn($url);

        $foundOnUriInterfaceMock = $this->createMock(UriInterface::class);
        $foundOnUriInterfaceMock->method('__toString')
            ->willReturn($foundOnUrl);

        $loggerInterfaceMock = $this->createMock(LoggerInterface::class);
        $loggerInterfaceMock->expects($this->once())
            ->method('info')
            ->with("hasBeenCrawled: {$url} - found on {$foundOnUrl}");

        $responseInterfaceMock = $this->createMock(ResponseInterface::class);

        $crawlObserver = new LoggingCrawlObserver($loggerInterfaceMock);
        $crawlObserver->crawled($uriInterfaceMock, $responseInterfaceMock, $foundOnUriInterfaceMock);
    }

    public function testCrawlFailedLoggingWithoutFoundOnUrl()
    {
        $url = "http://www.foo.bar";

        $uriInterfaceMock = $this->createMock(UriInterface::class);
        $uriInterfaceMock->method('__toString')
            ->willReturn($url);

        $errorMessage = "An error message!";

        $requestExceptionMock = $this->createMock(RequestException::class);
        $this->setObjectAttribute($requestExceptionMock, 'message', $errorMessage);

        $loggerInterfaceStub = $this->createMock(LoggerInterface::class);
        $loggerInterfaceStub->expects($this->once())
            ->method('error')
            ->with("crawlFailed: {$url} - exception: {$errorMessage}");

        $crawlObserver = new LoggingCrawlObserver($loggerInterfaceStub);
        $crawlObserver->crawlFailed($uriInterfaceMock, $requestExceptionMock);
    }

    /**
     * @throws \ReflectionException
     */
    public function testCrawlFailedLoggingWithFoundOnUrl()
    {
        $url = "http://www.foo.bar/bar.html";
        $foundOnUrl = "http://www.foo.bar";

        $uriInterfaceMock = $this->createMock(UriInterface::class);
        $uriInterfaceMock->method('__toString')
            ->willReturn($url);

        $foundOnUriInterfaceMock = $this->createMock(UriInterface::class);
        $foundOnUriInterfaceMock->method('__toString')
            ->willReturn($foundOnUrl);

        $errorMessage = "An error message!";

        $requestExceptionMock = $this->createMock(RequestException::class);
        $this->setObjectAttribute($requestExceptionMock, 'message', $errorMessage);

        $loggerInterfaceStub = $this->createMock(LoggerInterface::class);
        $loggerInterfaceStub->expects($this->once())
            ->method('error')
            ->with("crawlFailed: {$url} - found on {$foundOnUrl} - exception: {$errorMessage}");

        $crawlObserver = new LoggingCrawlObserver($loggerInterfaceStub);
        $crawlObserver->crawlFailed($uriInterfaceMock, $requestExceptionMock, $foundOnUriInterfaceMock);
    }

    public function testFinishedCrawlingLogging()
    {
        $loggerInterfaceStub = $this->createMock(LoggerInterface::class);
        $loggerInterfaceStub->expects($this->once())
            ->method('info')
            ->with("finishedCrawling");

        $crawlObserver = new LoggingCrawlObserver($loggerInterfaceStub);
        $crawlObserver->finishedCrawling();
    }
}
