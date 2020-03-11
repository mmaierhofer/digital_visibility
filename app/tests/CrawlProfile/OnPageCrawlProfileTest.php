<?php

namespace DigitalVisibilityIndex\CrawlProfile;

use DigitalVisibilityIndexTests\CrawlProfile\OnPageCrawlProfileTest;

function get_headers($url, $format)
{
    return OnPageCrawlProfileTest::$functions->get_headers($url, $format);
}

namespace DigitalVisibilityIndexTests\CrawlProfile;

use DigitalVisibilityIndex\CrawlProfile\OnPageCrawlProfile;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

/**
 * Class OnPageCrawlProfileTest
 * @package DigitalVisibilityIndexTests\CrawlProfile
 */
class OnPageCrawlProfileTest extends TestCase
{
    public static $functions;

    public function setUp(): void
    {
        self::$functions = Mockery::mock();
    }

    public function testShouldCrawlWithExternalUrlAndHtmlContentType()
    {
        $baseUrl = 'http://www.foo.bar';
        $crawlUrl = 'http://www.foo.bar/foo';

        $crawlUrlMock = $this->createMock(UriInterface::class);
        $crawlUrlMock->method('getHost')
            ->willReturn('www.bar.foo');
        $crawlUrlMock->method('__toString')
            ->willReturn($crawlUrl);

        self::$functions
            ->shouldReceive('get_headers')
            ->andReturn(['Content-Type' => 'text/html; charset=UTF-8']);

        $crawlProfile = new OnPageCrawlProfile($baseUrl);
        $result = $crawlProfile->shouldCrawl($crawlUrlMock);

        $this->assertFalse($result);
    }

    public function testShouldCrawlWithExternalUrlAndOtherContentType()
    {
        $baseUrl = 'http://www.foo.bar';
        $crawlUrl = 'http://www.foo.bar/foo';

        $crawlUrlMock = $this->createMock(UriInterface::class);
        $crawlUrlMock->method('getHost')
            ->willReturn('www.bar.foo');
        $crawlUrlMock->method('__toString')
            ->willReturn($crawlUrl);

        self::$functions
            ->shouldReceive('get_headers')
            ->andReturn(['Content-Type' => 'text/plain; charset=UTF-8']);

        $crawlProfile = new OnPageCrawlProfile($baseUrl);
        $result = $crawlProfile->shouldCrawl($crawlUrlMock);

        $this->assertFalse($result);
    }

    public function testShouldCrawlWithInternalUrlAndHtmlContentType()
    {
        $baseUrl = 'http://www.foo.bar';
        $crawlUrl = 'http://www.foo.bar/foo';

        $crawlUrlMock = $this->createMock(UriInterface::class);
        $crawlUrlMock->method('getHost')
            ->willReturn('www.foo.bar');
        $crawlUrlMock->method('__toString')
            ->willReturn($crawlUrl);

        self::$functions
            ->shouldReceive('get_headers')
            ->andReturn(['Content-Type' => 'text/html; charset=UTF-8']);

        $crawlProfile = new OnPageCrawlProfile($baseUrl);
        $result = $crawlProfile->shouldCrawl($crawlUrlMock);

        $this->assertTrue($result);
    }

    public function testShouldCrawlWithInternalUrlAndOtherContentType()
    {
        $baseUrl = 'http://www.foo.bar';
        $crawlUrl = 'http://www.foo.bar/foo';

        $crawlUrlMock = $this->createMock(UriInterface::class);
        $crawlUrlMock->method('getHost')
            ->willReturn('www.foo.bar');
        $crawlUrlMock->method('__toString')
            ->willReturn($crawlUrl);

        self::$functions
            ->shouldReceive('get_headers')
            ->andReturn(['Content-Type' => 'text/plain; charset=UTF-8']);

        $crawlProfile = new OnPageCrawlProfile($baseUrl);
        $result = $crawlProfile->shouldCrawl($crawlUrlMock);

        $this->assertFalse($result);
    }

    public function testShouldCrawlWithInternalUrlAndHtmlContentTypeArray()
    {
        $baseUrl = 'http://www.foo.bar';
        $crawlUrl = 'http://www.foo.bar/foo';

        $crawlUrlMock = $this->createMock(UriInterface::class);
        $crawlUrlMock->method('getHost')
            ->willReturn('www.foo.bar');
        $crawlUrlMock->method('__toString')
            ->willReturn($crawlUrl);

        self::$functions
            ->shouldReceive('get_headers')
            ->andReturn([
                'Content-Type' => [
                    'text/html; charset=UTF-8',
                    'text/html; charset=UTF-8'
                ]
            ]);

        $crawlProfile = new OnPageCrawlProfile($baseUrl);
        $result = $crawlProfile->shouldCrawl($crawlUrlMock);

        $this->assertTrue($result);
    }
}
