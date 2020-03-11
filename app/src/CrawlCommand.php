<?php

namespace DigitalVisibilityIndex;

use DigitalVisibilityIndex\Configuration\JSONConfiguration;
use DigitalVisibilityIndex\CrawlObserver\EmailAddressesCrawlObserver;
use DigitalVisibilityIndex\CrawlObserver\LocationServiceCrawlObserver;
use DigitalVisibilityIndex\CrawlObserver\HomepageScreenshotsCrawlObserver;
use DigitalVisibilityIndex\CrawlObserver\MobileOptimizationCrawlObserver;
use DigitalVisibilityIndex\CrawlObserver\HomepageSpeedIndexesCrawlObserver;
use DigitalVisibilityIndex\CrawlObserver\BusinessDirectoryLinkCrawlObserver;
use DigitalVisibilityIndex\CrawlObserver\LoggingCrawlObserver;
use DigitalVisibilityIndex\CrawlObserver\OnlineShopCrawlObserver;
use DigitalVisibilityIndex\CrawlObserver\SocialNetworkLinkCrawlObserver;
use DigitalVisibilityIndex\CrawlObserver\VideoCrawlObserver;
use DigitalVisibilityIndex\CrawlObserver\TelephoneNumberCrawlObserver;
use DigitalVisibilityIndex\CrawlObserver\OpeningHoursCrawlObserver;
use DigitalVisibilityIndex\CrawlProfile\OnPageCrawlProfile;
use DigitalVisibilityIndex\Storage\SimpleStorage;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Spatie\Crawler\Crawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Spatie\Browsershot\Browsershot;

/**
 * Class CrawlCommand
 * @package DigitalVisibilityIndex
 */
class CrawlCommand extends Command
{
    protected function configure()
    {
        $this->setName('crawl')
            ->setDescription('Analyse the on-page optimization for digital visibility of a website.')
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'The url of the website to analyse'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl = $input->getArgument('url');
        $crawlProfile = new OnPageCrawlProfile($baseUrl);

        $browsershot = (new Browsershot())
            ->noSandbox();

        $storage = new SimpleStorage();
        $configuration = new JSONConfiguration(dirname(__FILE__) . '/../config/config.json');

        $streamHandler = new StreamHandler('php://stdout', Logger::DEBUG);
        $streamHandler->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n"));

        $logger = new Logger('OnPageCrawlerLog');
        $logger->pushHandler($streamHandler);

        $loggerCrawlObserver = new LoggingCrawlObserver($logger);
        $emailCrawlObserver = new EmailAddressesCrawlObserver($storage);
        $locationServiceObserver = new LocationServiceCrawlObserver($storage, $configuration);
        $telephoneCrawlObserver = new TelephoneNumberCrawlObserver($storage);
        $socialNetworkLinkCrawlObserver = new SocialNetworkLinkCrawlObserver($storage, $configuration);
        $businessNetworkLinkCrawlObserver = new BusinessDirectoryLinkCrawlObserver($storage, $configuration);
        $openingHoursCrawlObserver = new OpeningHoursCrawlObserver($storage, $configuration);
        $videoCrawlObserver = new VideoCrawlObserver($storage);
        $mobileOptimizationCrawlObserver = new MobileOptimizationCrawlObserver($storage);
        $homepageScreenshotsCrawlObserver = new HomepageScreenshotsCrawlObserver($storage, $configuration);
        $homepageSpeedIndexesCrawlObserver = new HomepageSpeedIndexesCrawlObserver($storage, $configuration);
        $onlineShopCrawlObserver = new OnlineShopCrawlObserver($storage, $configuration);

        Crawler::create()
            ->setBrowsershot($browsershot)
            ->executeJavaScript()
            ->setMaximumDepth(2)
            ->setMaximumCrawlCount(20)
            ->setCrawlProfile($crawlProfile)
            ->addCrawlObserver($loggerCrawlObserver)
            ->addCrawlObserver($emailCrawlObserver)
            ->addCrawlObserver($locationServiceObserver)
            ->addCrawlObserver($telephoneCrawlObserver)
            ->addCrawlObserver($socialNetworkLinkCrawlObserver)
            ->addCrawlObserver($businessNetworkLinkCrawlObserver)
            ->addCrawlObserver($openingHoursCrawlObserver)
            ->addCrawlObserver($videoCrawlObserver)
            ->addCrawlObserver($mobileOptimizationCrawlObserver)
            ->addCrawlObserver($homepageScreenshotsCrawlObserver)
            ->addCrawlObserver($homepageSpeedIndexesCrawlObserver)
            ->addCrawlObserver($onlineShopCrawlObserver)
            ->startCrawling($baseUrl);

        print_r($storage);

        return 0;
    }
}
