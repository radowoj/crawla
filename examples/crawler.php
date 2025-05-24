<?php

declare(strict_types=1);

use Symfony\Component\DomCrawler\Crawler as DomCrawler;

require_once '../vendor/autoload.php';

$crawler = new \Radowoj\Crawla\Crawler(
    'https://github.com/radowoj',
    new \Symfony\Component\HttpClient\CurlHttpClient(),
);

$dataGathered = [];

//configure our crawler
//first - set CSS selector for links that should be visited
$crawler->setLinkSelector('.pinned-item-list-item-content a.Link')
    //second - define what should be done, when a page was visited?
    ->setPageVisitedCallback(function (DomCrawler $domCrawler) use (&$dataGathered) {
        //callback will be called for every visited page, including the base url, so let's ensure that
        //repo data will be gathered only on repo pages
        if (!preg_match('/radowoj\/\w+/', $domCrawler->getUri())) {
            return;
        }

        $readme = $domCrawler->filter('article.markdown-body');

        $dataGathered[] = [
            'title' => trim($domCrawler->filter('p.f4.my-3')->text()),
            'readme' => $readme->count() ? trim($readme->text()) : '',
        ];
    });

//now crawl, following up to 1 links deep from the entry point
$crawler->crawl(1);

var_dump($dataGathered);

var_dump($crawler->getVisited()->all());
