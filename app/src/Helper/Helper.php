<?php

namespace DigitalVisibilityIndex\Helper;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Helper
 * @package DigitalVisibilityIndex\Helper
 */
class Helper
{
    /**
     * @param string $html
     * @return string
     */
    public static function tidyHtml($html)
    {
        // Remove HTML comments
        $html = preg_replace('/<!--.*?-->/ms', '', $html);

        $crawler = new Crawler($html);

        // Remove inline code
        $tags = ['script', 'style', 'link', 'svg'];

        foreach ($tags as $t) {
            $crawler->filterXPath('//' . $t)->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });
        }

        return $crawler->html();
    }
}
