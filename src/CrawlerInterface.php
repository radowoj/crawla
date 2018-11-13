<?php

declare(strict_types=1);

namespace Radowoj\Crawla;

use GuzzleHttp\ClientInterface;
use Radowoj\Crawla\Link\CollectionInterface;

interface CrawlerInterface
{
    /**
     * @param string $linkSelector
     *
     * @return CrawlerInterface
     */
    public function setLinkSelector(string $linkSelector): self;

    /**
     * @param ClientInterface $client
     *
     * @return CrawlerInterface
     */
    public function setClient(ClientInterface $client): self;

    /**
     * @return CollectionInterface
     */
    public function getVisited(): CollectionInterface;

    /**
     * @return CollectionInterface
     */
    public function getQueued(): CollectionInterface;

    /**
     * @return CollectionInterface
     */
    public function getTooDeep(): CollectionInterface;

    /**
     * @param callable $urlValidatorCallback
     *
     * @return CrawlerInterface
     */
    public function setUrlValidatorCallback(callable $urlValidatorCallback): self;

    /**
     * @param int $maxDepth
     *
     * @return mixed
     */
    public function crawl(int $maxDepth = Crawler::DEPTH_DEFAULT);
}
