<?php

declare(strict_types=1);

namespace Radowoj\Crawla\Link;

use Countable;
use InvalidArgumentException;
use Radowoj\Crawla\Exception\InvalidUrlException;

class Collection implements Countable, CollectionInterface
{
    /**
     * Items stored in this collection
     * array keys - urls
     * array values - depths.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Collection constructor.
     *
     * @param array $sourceArray [url => depth]
     */
    public function __construct(array $sourceArray = [])
    {
        $this->items = [];
        $this->appendArray($sourceArray);

        return $this;
    }

    /**
     * @param Link $link
     *
     * @return CollectionInterface
     */
    public function push(Link $link): CollectionInterface
    {
        $this->items[$link->getUrl()] = $link->getDepth();

        return $this;
    }

    /**
     * @param array $urls
     * @param int   $depth
     *
     * @return CollectionInterface
     */
    public function appendUrlsAtDepth(array $urls, int $depth = 0): CollectionInterface
    {
        $sourceArray = array_fill_keys($urls, $depth);
        $this->appendArray($sourceArray);

        return $this;
    }

    /**
     * @param int|null $depth
     *
     * @return array
     */
    public function all(int $depth = null): array
    {
        return null === $depth
            ? array_keys($this->items)
            : array_keys($this->items, $depth, true);
    }

    /**
     * @return null|Link
     */
    public function shift(): ?Link
    {
        if (!$this->items) {
            return null;
        }

        $depth = reset($this->items);
        $url = key($this->items);
        unset($this->items[$url]);

        return new Link($url, $depth);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return \count($this->items);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @param array $sourceArray
     */
    private function appendArray(array $sourceArray): void
    {
        foreach ($sourceArray as $url => $depth) {
            if (!\is_string($url)) {
                throw new InvalidArgumentException('Source array key must be a string (url)');
            }
            if (!\is_int($depth) || $depth < 0) {
                throw new InvalidArgumentException('Source array value must be a non-negative int (depth)');
            }

            if (!\filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidUrlException("Provided URL is invalid: {$url}");
            }
        }
        $this->items = array_merge($sourceArray, $this->items);
    }
}
