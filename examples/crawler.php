<?php

declare(strict_types=1);

use Symfony\Component\DomCrawler\Crawler as DomCrawler;

require_once '../vendor/autoload.php';

$crawler = new \Radowoj\Crawla\Crawler(
    'https://github.com/radowoj'
);

$dataGathered = [];

//configure our crawler
//first - set CSS selector for links that should be visited
$crawler->setLinkSelector('span.pinned-repo-item-content span.d-block a.text-bold')

    //second - customize guzzle client used for requests
    ->setClient(new GuzzleHttp\Client([
        GuzzleHttp\RequestOptions::DELAY => 100,
    ]))

    //third - define what should be done, when a page was visited?
    ->setPageVisitedCallback(function (DomCrawler $domCrawler) use (&$dataGathered) {
        //callback will be called for every visited page, including the base url, so let's ensure that
        //repo data will be gathered only on repo pages
        if (!preg_match('/radowoj\/\w+/', $domCrawler->getUri())) {
            return;
        }

        $readme = $domCrawler->filter('#readme');

        $dataGathered[] = [
            'title' => trim($domCrawler->filter('span[itemprop="about"]')->text()),
            'commits' => trim($domCrawler->filter('li.commits span.num')->text()),
            'readme' => $readme->count() ? trim($readme->text()) : '',
        ];
    });

//now crawl, following up to 1 links deep from the entry point
$crawler->crawl(1);

var_dump($dataGathered);

var_dump($crawler->getVisited()->all());
