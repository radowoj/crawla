<?php

declare(strict_types=1);

namespace Radowoj\Crawla;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Radowoj\Crawla\Link\Collection as LinkCollection;
use Radowoj\Crawla\Link\CollectionInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Crawler implements CrawlerInterface
{
    /**
     * Constants for typical depths of crawling.
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
     * Collection of already visited urls.
     *
     * @var CollectionInterface
     */
    protected $linksVisited = null;

    /**
     * Collection of urls queued to visit.
     *
     * @var CollectionInterface
     */
    protected $linksQueued = null;

    /**
     * Collection of urls found, but too deep to visit.
     *
     * @var CollectionInterface
     */
    protected $linksTooDeep = null;

    /**
     * @var string
     */
    protected $baseUrl = '';

    /**
     * @var callable | null
     */
    protected $urlValidatorCallback = null;

    /**
     * @var callable | null
     */
    protected $pageVisitedCallback = null;

    /**
     * @var int
     */
    protected $maxDepth = self::DEPTH_INFINITE;

    /**
     * Crawler constructor.
     *
     * @param string $baseUrl
     */
    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->linksVisited = new LinkCollection();
        $this->linksQueued = new LinkCollection();
        $this->linksTooDeep = new LinkCollection();
    }

    /**
     * Gets current link selector.
     *
     * @return string
     */
    public function getLinkSelector(): string
    {
        return $this->linkSelector;
    }

    /**
     * Sets CSS selector for links that crawler should follow.
     *
     * @param string $linkSelector
     *
     * @return CrawlerInterface
     */
    public function setLinkSelector(string $linkSelector): CrawlerInterface
    {
        $this->linkSelector = $linkSelector;

        return $this;
    }

    /**
     * Injects HTTP client (custom configured Guzzle client for example).
     *
     * @param ClientInterface $client
     * @return CrawlerInterface
     */
    public function setClient(ClientInterface $client): CrawlerInterface
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Returns client instance (creates new default Guzzle Client, if client has not been set previously).
     *
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
     * @param CollectionInterface $linksVisited
     *
     * @return Crawler
     */
    public function setVisited(CollectionInterface $linksVisited): CrawlerInterface
    {
        $this->linksVisited = $linksVisited;

        return $this;
    }

    /**
     * @param CollectionInterface $linksQueued
     *
     * @return Crawler
     */
    public function setQueued(CollectionInterface $linksQueued): CrawlerInterface
    {
        $this->linksQueued = $linksQueued;

        return $this;
    }

    /**
     * Returns visited links collection (creates empty if not set).
     *
     * @return CollectionInterface
     */
    public function getVisited(): CollectionInterface
    {
        return $this->linksVisited;
    }

    /**
     * Returns queued links collection (creates empty if not set).
     *
     * @return CollectionInterface
     */
    public function getQueued(): CollectionInterface
    {
        return $this->linksQueued;
    }

    /**
     * Returns too deep to visit links collection (creates empty if not set).
     *
     * @return CollectionInterface
     */
    public function getTooDeep(): CollectionInterface
    {
        return $this->linksTooDeep;
    }

    /**
     * Sets callback that will be called when discovering a link (to determine if it should be queued for visiting).
     *
     * @param callable $urlValidatorCallback
     *
     * @return CrawlerInterface
     */
    public function setUrlValidatorCallback(callable $urlValidatorCallback): CrawlerInterface
    {
        $this->urlValidatorCallback = $urlValidatorCallback;

        return $this;
    }

    /**
     * @param callable $pageVisitedCallback
     *
     * @return CrawlerInterface
     */
    public function setPageVisitedCallback(callable $pageVisitedCallback): CrawlerInterface
    {
        $this->pageVisitedCallback = $pageVisitedCallback;

        return $this;
    }

    /**
     * Start crawling.
     *
     * @param int $maxDepth - max visits depth
     *
     * @return bool
     */
    public function crawl(int $maxDepth = self::DEPTH_DEFAULT)
    {
        $this->maxDepth = $maxDepth;
        $this->getQueued()->appendMany([$this->baseUrl], 0);
        $this->crawlPages();

        return true;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function crawlPages()
    {
        while ($link = $this->getQueued()->shift()) {
            if (self::DEPTH_INFINITE !== $this->maxDepth && $link->getDepth() > $this->maxDepth) {
                $this->getTooDeep()->push($link);
                continue;
            }

            $response = $this->getClient()->request('GET', $link->getUrl());
            if (200 !== $response->getStatusCode()) {
                continue;
            }

            $this->getVisited()->push($link);

            $domCrawler = new DomCrawler(
                (string) $response->getBody(),
                $link->getUrl()
            );

            if (\is_callable($this->pageVisitedCallback)) {
                \call_user_func($this->pageVisitedCallback, $domCrawler);
            }

            $urls = $this->getUrls($domCrawler);
            $urls = $this->filterUrls($urls);
            $this->queueUrls($link->getDepth() + 1, $urls);
        }
    }

    /**
     * Default url validator.
     *
     * @param $url
     *
     * @return bool
     */
    protected function isWithinBaseUrl($url): bool
    {
        return 0 === mb_strpos($url, $this->baseUrl);
    }

    /**
     * @param DomCrawler $domCrawler
     *
     * @return array
     */
    protected function getUrls(DomCrawler $domCrawler): array
    {
        $links = $domCrawler->filter($this->linkSelector)->links();

        $urls = array_map(function ($link) {
            $url = $link->getUri();
            $url = explode('#', $url);

            return $url[0];
        }, $links);

        $urls = array_unique($urls);

        return $urls;
    }

    /**
     * @param array $urls
     *
     * @return array
     */
    protected function filterUrls(array $urls): array
    {
        $urlConstraintCallback = \is_callable($this->urlValidatorCallback)
            ? $this->urlValidatorCallback
            : [$this, 'isWithinBaseUrl'];

        $urls = array_filter($urls, $urlConstraintCallback);

        return $urls;
    }

    /**
     * @param int   $depth
     * @param array $urls
     */
    protected function queueUrls(int $depth, array $urls): void
    {
        $this->getQueued()->appendMany(
            array_diff(
                $urls,
                $this->getQueued()->all(),
                $this->getVisited()->all()
            ),
            $depth
        );
    }
}
