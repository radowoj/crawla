<?php

namespace Radowoj\Crawla;

use GuzzleHttp\ClientInterface;
use Radowoj\Crawla\Link\CollectionInterface;

interface CrawlerInterface
{
    /**
     * Gets current link selector.
     *
     * @return string
     */
    public function getLinkSelector(): string;

    /**
     * Sets CSS selector for links that crawler should follow.
     *
     * @param string $linkSelector
     *
     * @return \Radowoj\Crawla\CrawlerInterface
     */
    public function setLinkSelector(string $linkSelector): CrawlerInterface;

    /**
     * @param CollectionInterface $linksVisited
     *
     * @return Crawler
     */
    public function setVisited(CollectionInterface $linksVisited): CrawlerInterface;

    /**
     * @param CollectionInterface $linksQueued
     *
     * @return Crawler
     */
    public function setQueued(CollectionInterface $linksQueued): CrawlerInterface;

    /**
     * Returns visited links collection (creates empty if not set).
     *
     * @return CollectionInterface
     */
    public function getVisited(): CollectionInterface;

    /**
     * Returns queued links collection (creates empty if not set).
     *
     * @return CollectionInterface
     */
    public function getQueued(): CollectionInterface;

    /**
     * Returns too deep to visit links collection (creates empty if not set).
     *
     * @return CollectionInterface
     */
    public function getTooDeep(): CollectionInterface;

    /**
     * Sets callback that will be called when discovering a link (to determine if it should be queued for visiting).
     *
     * @param callable $urlValidatorCallback
     *
     * @return \Radowoj\Crawla\CrawlerInterface
     */
    public function setUrlValidatorCallback(callable $urlValidatorCallback): CrawlerInterface;

    /**
     * @param callable $pageVisitedCallback
     *
     * @return \Radowoj\Crawla\CrawlerInterface
     */
    public function setPageVisitedCallback(callable $pageVisitedCallback): CrawlerInterface;

    /**
     * Start crawling.
     *
     * @param int $maxDepth - max visits depth
     *
     * @return bool
     */
    public function crawl(int $maxDepth = self::DEPTH_DEFAULT);
}