<?php
/**
 * @author Radosław Wojtyczka <radoslaw.wojtyczka@gmail.com>
 */

namespace Radowoj\Crawla;

use GuzzleHttp\ClientInterface;
use Radowoj\Crawla\Link\Collection as LinkCollection;

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
     * @return LinkCollection
     */
    public function getVisited();

    /**
     * @return LinkCollection
     */
    public function getQueued();

    /**
     * @return LinkCollection
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