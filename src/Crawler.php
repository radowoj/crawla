<?php

declare(strict_types=1);

namespace Radowoj\Crawla;

use Radowoj\Crawla\Link\Collection as LinkCollection;
use Radowoj\Crawla\Link\CollectionInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Crawler implements CrawlerInterface
{
    public const DEPTH_ONLY_TARGET = 0;
    public const DEPTH_DEFAULT = 2;
    public const DEPTH_INFINITE = -100;

    private string $linkSelector = 'a';
    /** @var callable|null  */
    private $urlValidatorCallback = null;
    /** @var callable|null  */
    private $pageVisitedCallback = null;
    private int $maxDepth = self::DEPTH_INFINITE;

    public function __construct(
        private string $baseUrl,
        private HttpClientInterface $client,
        private CollectionInterface $linksVisited = new LinkCollection(),
        private CollectionInterface $linksQueued = new LinkCollection(),
        private CollectionInterface $linksTooDeep = new LinkCollection(),
    ) {
    }

    public function getLinkSelector(): string
    {
        return $this->linkSelector;
    }

    public function setLinkSelector(string $linkSelector): CrawlerInterface
    {
        $this->linkSelector = $linkSelector;

        return $this;
    }

    public function setVisited(CollectionInterface $linksVisited): CrawlerInterface
    {
        $this->linksVisited = $linksVisited;

        return $this;
    }

    public function setQueued(CollectionInterface $linksQueued): CrawlerInterface
    {
        $this->linksQueued = $linksQueued;

        return $this;
    }

    public function getVisited(): CollectionInterface
    {
        return $this->linksVisited;
    }

    public function getQueued(): CollectionInterface
    {
        return $this->linksQueued;
    }

    public function getTooDeep(): CollectionInterface
    {
        return $this->linksTooDeep;
    }

    public function setUrlValidatorCallback(callable $urlValidatorCallback): CrawlerInterface
    {
        $this->urlValidatorCallback = $urlValidatorCallback;

        return $this;
    }

    public function setPageVisitedCallback(callable $pageVisitedCallback): CrawlerInterface
    {
        $this->pageVisitedCallback = $pageVisitedCallback;

        return $this;
    }

    public function crawl(int $maxDepth = self::DEPTH_DEFAULT): void
    {
        $this->maxDepth = $maxDepth;
        $this->getQueued()->appendUrlsAtDepth([$this->baseUrl], 0);
        $this->crawlPages();
    }

    private function crawlPages(): void
    {
        while ($link = $this->getQueued()->shift()) {
            if (self::DEPTH_INFINITE !== $this->maxDepth && $link->getDepth() > $this->maxDepth) {
                $this->getTooDeep()->push($link);
                continue;
            }

            $response = $this->client->request('GET', $link->getUrl());
            if (200 !== $response->getStatusCode()) {
                continue;
            }

            $this->getVisited()->push($link);

            $domCrawler = new DomCrawler(
                (string) $response->getContent(),
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

    private function isWithinBaseUrl($url): bool
    {
        return 0 === mb_strpos($url, $this->baseUrl);
    }

    private function getUrls(DomCrawler $domCrawler): array
    {
        $links = $domCrawler->filter($this->linkSelector)->links();

        $urls = array_map(function ($link) {
            $url = $link->getUri();
            $url = explode('#', $url);

            return $url[0];
        }, $links);

        return array_unique($urls);
    }

    private function filterUrls(array $urls): array
    {
        $urlConstraintCallback = \is_callable($this->urlValidatorCallback)
            ? $this->urlValidatorCallback
            : $this->isWithinBaseUrl(...);

        return array_filter($urls, $urlConstraintCallback);
    }

    private function queueUrls(int $depth, array $urls): void
    {
        $this->getQueued()->appendUrlsAtDepth(
            array_diff(
                $urls,
                $this->getQueued()->all(),
                $this->getVisited()->all()
            ),
            $depth
        );
    }
}
