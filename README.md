# Crawla - a simple web crawler library

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/radowoj/crawla/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/radowoj/crawla/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/radowoj/crawla/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/radowoj/crawla/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/radowoj/crawla/badges/build.png?b=master)](https://scrutinizer-ci.com/g/radowoj/crawla/build-status/master)



## Installation

Via composer

```bash
$ composer require radowoj/crawla
```

## Example 1 - get titles, counts of commits and readmes from pages linked from an entry point
```php
<?php

use Symfony\Component\DomCrawler\Crawler as DomCrawler;

require_once('../vendor/autoload.php');

$crawler = new \Radowoj\Crawla\Crawler(
    'https://github.com/radowoj'
);

$dataGathered = [];

//configure our crawler
//first - set CSS selector for links that should be visited
$crawler->setLinkSelector('span.pinned-repo-item-content span.d-block a.text-bold')

    //second - customize guzzle client used for requests
    ->setClient(new GuzzleHttp\Client([
        GuzzleHttp\RequestOptions::DELAY => 100
    ]))

    //third - define what should be done, when a page was visited?
    ->setPageVisitedCallback(function(DomCrawler $domCrawler) use(&$dataGathered) {
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
```


## Example 2 - simple site map

```php
<?php

require_once('../vendor/autoload.php');

$crawler = new \Radowoj\Crawla\Crawler(
    'https://developer.github.com/'
);

$dataGathered = [];

//configure our crawler
$crawler->setClient(new GuzzleHttp\Client([
        GuzzleHttp\RequestOptions::DELAY => 100
    ]))
    
    //set link selector (all links - this is the default value)
    ->setLinkSelector('a');

//check up to 1 levels deep
$crawler->crawl(1);

//get links of all visited pages
var_dump($crawler->getVisited()->all());

//get links that were too deep to visit
var_dump($crawler->getTooDeep()->all());
```
