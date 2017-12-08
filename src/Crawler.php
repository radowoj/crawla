<?php

declare(strict_types=1);

namespace Radowoj\Crawla;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Radowoj\Crawla\Link\Collection as LinkCollection;

class Crawler implements CrawlerInterface
{
    /**
     * Constants for typical depths of crawling
     */
    const DEPTH_ONLY_TARGET = 0;
    const DEPTH_DEFAULT = 2;
    const DEPTH_INFINITE = -100;


    /**
     * @var ClientInterface
     */
    protected $client = null;


    /**
     * @var string
     */
    protected $linkSelector = 'a';


    /**
     * Collection of already visited urls
     * @var LinkCollection
     */
    protected $visited = null;


    /**
     * Collection of urls queued to visit
     * @var LinkCollection
     */
    protected $queued = null;


    /**
     * Collection of urls found, but too deep to visit
     * @var LinkCollection
     */
    protected $urlsTooDeep = null;


    /**
     * @var string
     */
    protected $baseUrl = '';


    /**
     * @var callable | null
     */
    protected $urlValidatorCallback = null;


    /**
     * @var int
     */
    protected $maxDepth = self::DEPTH_INFINITE;


    /**
     * Crawler constructor.
     * @param string $baseUrl
     */
    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }


    /**
     * Gets current link selector
     * @return string
     */
    public function getLinkSelector(): string
    {
        return $this->linkSelector;
    }


    /**
     * Sets CSS selector for links that crawler should follow
     * @param string $linkSelector
     * @return CrawlerInterface
     */
    public function setLinkSelector(string $linkSelector) : CrawlerInterface
    {
        $this->linkSelector = $linkSelector;
        return $this;
    }


    /**
     * Injects HTTP client (custom configured Guzzle client for example)
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client): CrawlerInterface
    {
        $this->client = $client;
        return $this;
    }


    /**
     * Returns client instance (creates new default Guzzle Client, if client has not been set previously)
     * @return ClientInterface
     */
    public function getClient()
    {
        if (!$this->client instanceof ClientInterface) {
            $this->client = new Client();
        }
        return $this->client;
    }


    /**
     * Returns visited links collection (creates empty if not set)
     * @return LinkCollection
     */
    public function getVisited()
    {
        if (is_null($this->visited)) {
            $this->visited = new LinkCollection();
        }

        return $this->visited;
    }


    /**
     * Returns queued links collection (creates empty if not set)
     * @return LinkCollection
     */
    public function getQueued()
    {
        if (is_null($this->queued)) {
            $this->queued = new LinkCollection();
        }

        return $this->queued;
    }


    /**
     * Returns too deep to visit links collection (creates empty if not set)
     * @return LinkCollection
     */
    public function getTooDeep()
    {
        if (is_null($this->urlsTooDeep)) {
            $this->urlsTooDeep = new LinkCollection();
        }

        return $this->urlsTooDeep;
    }


    /**
     * Sets callback that will be called when discovering a link (to determine if it should be queued for visiting)
     * @param callable $urlValidatorCallback
     * @return CrawlerInterface
     */
    public function setUrlValidatorCallback(callable $urlValidatorCallback) : CrawlerInterface
    {
        $this->urlValidatorCallback = $urlValidatorCallback;
        return $this;
    }


    /**
     * Start crawling
     * @param int $maxDepth - max visits depth
     * @return bool
     */
    public function crawl(int $maxDepth = self::DEPTH_DEFAULT)
    {
        $this->maxDepth = $maxDepth;
        $this->getQueued()->append([$this->baseUrl], 0);
        $this->crawlPages();
        return true;
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function crawlPages()
    {
        while($page = $this->getQueued()->shift()) {
            if ($this->maxDepth !== self::DEPTH_INFINITE && $page['depth'] > $this->maxDepth) {
                $this->getTooDeep()->append([$page['url']], $page['depth']);
                continue;
            }

            $response = $this->getClient()->request('GET', $page['url']);
            if ($response->getStatusCode() !== 200) {
                continue;
            }

            $this->getVisited()->append([$page['url']], $page['depth']);
            $this->parseForLinks($response, $page['url'], $page['depth'] + 1);
        }
    }


    /**
     * Parses client response for new links to visit
     * @param Response $response
     * @param string $url current url
     * @param int $depth current depth
     */
    protected function parseForLinks(Response $response, string $url, int $depth) : void
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

        $urlConstraintCallback = is_callable($this->urlValidatorCallback)
            ? $this->urlValidatorCallback
            : [$this, 'isWithinBaseUrl'];

        $urls = array_filter($urls, $urlConstraintCallback);

        $this->getQueued()->append(
            array_diff(
                $urls,
                $this->getQueued()->all(),
                $this->getVisited()->all()
            ),
            $depth
        );
    }


    /**
     * Default url validator
     * @param $url
     * @return bool
     */
    protected function isWithinBaseUrl($url) : bool
    {
        return (strpos($url, $this->baseUrl) === 0);
    }

}