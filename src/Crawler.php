<?php

declare(strict_types=1);

namespace Radowoj\Crawla;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Radowoj\Crawla\Link\Collection as LinkCollection;
use Radowoj\Crawla\Link\Link;

class Crawler
{
    const INFINITE_DEPTH = -100;
    /**
     * @var ClientInterface
     */
    protected $client = null;

    /**
     * @var string
     */
    protected $linkSelector = 'a';


    /**
     * Array of already visited urls
     * @var LinkCollection
     */
    protected $urlsVisited = null;


    /**
     * Array of urls queued to visit
     * @var LinkCollection
     */
    protected $urlsQueued = null;


    /**
     * @var string
     */
    protected $baseUrl = '';


    /**
     * @var callable | null
     */
    protected $urlConstraintCallback = null;


    /**
     * Crawler constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client, string $baseUrl)
    {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
    }


    public function getUrlsVisited()
    {
        if (is_null($this->urlsVisited)) {
            $this->urlsVisited = new LinkCollection();
        }

        return $this->urlsVisited;
    }


    public function getUrlsQueued()
    {
        if (is_null($this->urlsQueued)) {
            $this->urlsQueued = new LinkCollection();
        }

        return $this->urlsQueued;
    }


    /**
     * Sets callback for checking if given url should be crawled
     * @param callable $urlConstraintCallback
     */
    public function setUrlConstraintCallback(callable $urlConstraintCallback)
    {
        $this->urlConstraintCallback = $urlConstraintCallback;
    }


    public function crawl(int $depth = self::INFINITE_DEPTH)
    {
        $this->getUrlsQueued()->append([$this->baseUrl], $depth);
        $this->crawlPages();
    }



    protected function crawlPages()
    {
        while($page = $this->getUrlsQueued()->next()) {
            if ($page['depth'] < 0 && $page['depth'] !== self::INFINITE_DEPTH) {
                continue;
            }

            echo "v={$this->getUrlsVisited()->count()}\tq={$this->getUrlsQueued()->count()}\td={$page['depth']}\t{$page['url']}\n";

            $response = $this->client->request('GET', $page['url']);
            if ($response->getStatusCode() !== 200) {
                continue;
            }

            $this->getUrlsVisited()->append([$page['url']]);
            $this->parseForLinks($response, $page['url'], $page['depth'] - 1);
        }
    }


    /**
     *
     * @param string $url
     * @param $response
     */
    protected function parseForLinks(Response $response, string $url, $depth) : void
    {
        $domCrawler = new \Symfony\Component\DomCrawler\Crawler(
            (string)$response->getBody(),
            $url
        );

        $links = $domCrawler->filter($this->linkSelector)->links();

        $urls = array_map(function ($link) {
            $url = $link->getUri();
            $url = explode('#', $url);
            return $url[0];
        }, $links);

        $urls = array_unique($urls);

        $urlConstraintCallback = is_callable($this->urlConstraintCallback)
            ? $this->urlConstraintCallback
            : [$this, 'isWithinBaseUrl'];

        $urls = array_filter($urls, $urlConstraintCallback);

        $this->getUrlsQueued()->append(
            array_diff(
                $urls,
                $this->getUrlsQueued()->all(),
                $this->getUrlsVisited()->all()
            ),
            $depth
        );
    }


    protected function isWithinBaseUrl($url) : bool
    {
        return (strpos($url, $this->baseUrl) === 0);
    }

}