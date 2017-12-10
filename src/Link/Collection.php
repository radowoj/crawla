<?php

namespace Radowoj\Crawla\Link;

use Countable;
use InvalidArgumentException;

class Collection implements Countable, CollectionInterface
{
    const ELEMENT_URL_KEY = 'url';
    const ELEMENT_DEPTH_KEY = 'depth';

    /**
     * Items stored in this collection
     * array keys - urls
     * array values - depths
     * @var array
     */
    protected $items = [];


    /**
     * Add one element
     * @param string $url
     * @param int $depth
     * @return CollectionInterface
     */
    public function push(string $url, int $depth = 0) : CollectionInterface
    {
        $this->items[$url] = $depth;
        return $this;
    }


    /**
     * Add multiple elements
     * @param array $urls
     * @param int $depth
     */
    public function append(array $urls, int $depth = 0) : CollectionInterface
    {
        $itemsWithDepths = array_fill_keys($urls, $depth);
        $this->items = array_merge($itemsWithDepths, $this->items);
        return $this;
    }


    protected function assertElementIsValid(array $element)
    {
        if (!isset($element[self::ELEMENT_URL_KEY])
            || !isset($element[self::ELEMENT_DEPTH_KEY])
            || !is_string($element[self::ELEMENT_URL_KEY])
            || !is_int($element[self::ELEMENT_DEPTH_KEY])
        ) {
            throw new InvalidArgumentException(
                'Pushed item must be an array [' . self::ELEMENT_URL_KEY
                . ' => url (string), ' . self::ELEMENT_DEPTH_KEY.' => depth (int)]'
            );
        }
    }


    public function pushElement(array $element)
    {
        $this->assertElementIsValid($element);
        return $this->push($element[self::ELEMENT_URL_KEY], $element[self::ELEMENT_DEPTH_KEY]);
    }



    /**
     * Get all stored urls
     * @return array
     */
    public function all(int $depth = null)
    {
        return is_null($depth)
            ? array_keys($this->items)
            : array_keys($this->items, $depth);
    }


    /**
     * Equivalent of array_shift() - retrieve first element and remove it from collection
     * @return array
     */
    public function shift() : array
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
     * Shortcut to translate a single collection element to array
     * @param $url
     * @param $depth
     * @return array
     */
    protected function element($url, $depth)
    {
        return [
            self::ELEMENT_URL_KEY => $url,
            self::ELEMENT_DEPTH_KEY => $depth
        ];
    }


    /**
     * Count collected items
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }


    public function toArray()
    {
        return $this->items;
    }


    public function fromArray(array $sourceArray)
    {
        $this->items = [];
        foreach($sourceArray as $url => $depth) {
            if (!is_string($url) || !is_int($depth)) {
                throw new InvalidArgumentException("Source array must consist of url (string) => depth (int) key-value pairs");
            }
        }
        $this->items = $sourceArray;
        return $this;
    }


    public function toAssoc()
    {
        $assoc = [];
        foreach($this->items as $url => $depth) {
            $assoc[] = $this->element($url, $depth);
        }
        return $assoc;
    }


    public function fromAssoc(array $sourceAssoc)
    {
        $this->items = [];
        foreach($sourceAssoc as $item) {
            $this->pushElement($item);
        }

    }



}