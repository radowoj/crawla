<?php

declare(strict_types=1);

namespace Radowoj\Crawla\Link;

use Countable;
use InvalidArgumentException;
use Radowoj\Crawla\Exception\InvalidUrlException;

/**
 * Class Collection.
 */
class Collection implements Countable, CollectionInterface
{
    const ELEMENT_URL_KEY = 'url';
    const ELEMENT_DEPTH_KEY = 'depth';

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
     * @param array $sourceArray
     */
    public function __construct(array $sourceArray = [])
    {
        $this->items = [];
        foreach ($sourceArray as $url => $depth) {
            if (!\is_string($url) || !\is_int($depth)) {
                throw new InvalidArgumentException('Source array must consist of url (string) => depth (int) key-value pairs');
            }

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidUrlException("Provided URL is invalid: {$url}");
            }
        }
        $this->items = $sourceArray;

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
    public function appendMany(array $urls, int $depth = 0): CollectionInterface
    {
        $itemsWithDepths = array_fill_keys($urls, $depth);
        $this->items = array_merge($itemsWithDepths, $this->items);

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
}
