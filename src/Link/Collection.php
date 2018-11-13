<?php

declare(strict_types=1);

namespace Radowoj\Crawla\Link;

use Countable;
use InvalidArgumentException;

/**
 * Class Collection
 * @package Radowoj\Crawla\Link
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
     * @param string $url
     * @param int $depth
     * @return CollectionInterface
     */
    public function push(string $url, int $depth = 0): CollectionInterface
    {
        $this->items[$url] = $depth;

        return $this;
    }

    /**
     * @param array $urls
     * @param int $depth
     * @return CollectionInterface
     */
    public function append(array $urls, int $depth = 0): CollectionInterface
    {
        $itemsWithDepths = array_fill_keys($urls, $depth);
        $this->items = array_merge($itemsWithDepths, $this->items);

        return $this;
    }

    /**
     * @param array $element
     */
    protected function assertElementIsValid(array $element)
    {
        if (!isset($element[self::ELEMENT_URL_KEY])
            || !isset($element[self::ELEMENT_DEPTH_KEY])
            || !\is_string($element[self::ELEMENT_URL_KEY])
            || !\is_int($element[self::ELEMENT_DEPTH_KEY])
        ) {
            throw new InvalidArgumentException(
                'Pushed item must be an array ['.self::ELEMENT_URL_KEY
                .' => url (string), '.self::ELEMENT_DEPTH_KEY.' => depth (int)]'
            );
        }
    }

    /**
     * @param array $element
     * @return CollectionInterface
     */
    public function pushElement(array $element)
    {
        $this->assertElementIsValid($element);

        return $this->push($element[self::ELEMENT_URL_KEY], $element[self::ELEMENT_DEPTH_KEY]);
    }

    /**
     * @param int|null $depth
     * @return array
     */
    public function all(int $depth = null): array
    {
        return null === $depth
            ? array_keys($this->items)
            : array_keys($this->items, $depth, true);
    }

    /**
     * @return array
     */
    public function shift(): array
    {
        if (!$this->items) {
            return [];
        }

        $depth = reset($this->items);
        $url = key($this->items);
        unset($this->items[$url]);

        return $this->element($url, $depth);
    }

    /**
     * @param $url
     * @param $depth
     * @return array
     */
    protected function element($url, $depth): array
    {
        return [
            self::ELEMENT_URL_KEY => $url,
            self::ELEMENT_DEPTH_KEY => $depth,
        ];
    }

    /**
     * @return int
     */
    public function count()
    {
        return \count($this->items);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->items;
    }

    /**
     * @param array $sourceArray
     * @return $this
     */
    public function fromArray(array $sourceArray)
    {
        $this->items = [];
        foreach ($sourceArray as $url => $depth) {
            if (!\is_string($url) || !\is_int($depth)) {
                throw new InvalidArgumentException('Source array must consist of url (string) => depth (int) key-value pairs');
            }
        }
        $this->items = $sourceArray;

        return $this;
    }

    /**
     * @return array
     */
    public function toAssoc()
    {
        $assoc = [];
        foreach ($this->items as $url => $depth) {
            $assoc[] = $this->element($url, $depth);
        }

        return $assoc;
    }

    /**
     * @param array $sourceAssoc
     */
    public function fromAssoc(array $sourceAssoc)
    {
        $this->items = [];
        foreach ($sourceAssoc as $item) {
            $this->pushElement($item);
        }
    }
}
