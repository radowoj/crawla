<?php
/**
 * @author Radosław Wojtyczka <radoslaw.wojtyczka@gmail.com>
 */

namespace Radowoj\Crawla;

use GuzzleHttp\ClientInterface;
use Radowoj\Crawla\Link\Collection as LinkCollection;
use Radowoj\Crawla\Link\CollectionInterface;

interface CrawlerInterface
{
    /**
     * @param string $linkSelector
     * @return \Radowoj\Crawla\CrawlerInterface
     */
    public function setLinkSelector(string $linkSelector): CrawlerInterface;

    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client): CrawlerInterface;

    /**
     * @return CollectionInterface
     */
    public function getVisited();

    /**
     * @return CollectionInterface
     */
    public function getQueued();

    /**
     * @return CollectionInterface
     */
    public function getTooDeep();

    /**
     * @param callable $urlValidatorCallback
     * @return \Radowoj\Crawla\CrawlerInterface
     */
    public function setUrlValidatorCallback(callable $urlValidatorCallback): CrawlerInterface;

    /**
     * @param int $maxDepth - max visits depth
     * @return bool
     */
    public function crawl(int $maxDepth = self::DEPTH_DEFAULT);
}