<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

$crawler = new \Radowoj\Crawla\Crawler(
    'https://docs.github.com/',
    new \Symfony\Component\HttpClient\CurlHttpClient(),
);

$dataGathered = [];

//set link selector (all links - this is the default value)
$crawler->setLinkSelector('a');

//check up to 1 levels deep
$crawler->crawl(1);

//get links of all visited pages
var_dump($crawler->getVisited()->all());

//get links that were too deep to visit
var_dump($crawler->getTooDeep()->all());
