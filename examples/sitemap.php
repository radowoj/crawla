<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

$crawler = new \Radowoj\Crawla\Crawler(
    'https://developer.github.com/'
);

$dataGathered = [];

//configure our crawler
$crawler->setClient(new GuzzleHttp\Client([
    GuzzleHttp\RequestOptions::DELAY => 100,
]))

    //set link selector (all links - this is the default value)
    ->setLinkSelector('a');

//check up to 1 levels deep
$crawler->crawl(1);

//get links of all visited pages
var_dump($crawler->getVisited()->all());

//get links that were too deep to visit
var_dump($crawler->getTooDeep()->all());
