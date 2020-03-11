<?php

namespace DigitalVisibilityIndex\CrawlObserver;

use DigitalVisibilityIndex\Helper\Helper;
use DigitalVisibilityIndex\Storage\Storage;
use GuzzleHttp\Exception\RequestException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObserver;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class TelephoneNumberCrawlObserver
 * @package DigitalVisibilityIndex\CrawlObserver
 */
class TelephoneNumberCrawlObserver extends CrawlObserver
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var array
     */
    private $telephoneNumbers;

    /**
     * TelephoneNumberCrawlObserver constructor.
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
        $this->telephoneNumbers = [];
    }

    /**
     * @param UriInterface $url
     * @param ResponseInterface $response
     * @param UriInterface|null $foundOnUrl
     */
    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null
    ) {
        $crawler = new Crawler(Helper::tidyHtml((string)$response->getBody()));
        $html = $crawler->filterXPath('//body')->text();
        $html = trim(preg_replace('/\s+/', '', $html));

        // ((\+49|0)(\(*0\)*)?\(?[0-9]{2,}\)?[\/\-]?[0-9]{3,})
        $pattern = '/((\+49|0)(\(*0\)*)?\(?[0-9]{2,}\)?[\/\-]?[0-9]{3,})/x';
        preg_match_all($pattern, $html, $matches);

        $phoneNumberUtility = PhoneNumberUtil::getInstance();

        foreach ($matches[0] as $telephoneNumber) {
            try {
                $number = $phoneNumberUtility->parse($telephoneNumber, 'DE');

                if ($phoneNumberUtility->isValidNumber($number)) {
                    $formatedNumber = $phoneNumberUtility->format(
                        $number,
                        PhoneNumberFormat::E164
                    );

                    if (!in_array($formatedNumber, $this->telephoneNumbers)) {
                        array_push($this->telephoneNumbers, $formatedNumber);
                    }
                }
            } catch (NumberParseException $e) {
                // echo $e->getMessage();
            }
        }
    }

    /**
     * @param UriInterface $url
     * @param RequestException $requestException
     * @param UriInterface|null $foundOnUrl
     */
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null
    ) {
    }

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling()
    {
        $this->storage->setValue('telephoneNumbers', $this->telephoneNumbers);
    }
}
