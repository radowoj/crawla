<?php

namespace Radowoj\Crawla\Link;


class Collection implements CollectionInterface
{
    protected $items = [];

    public function append(array $urls, int $depth = 0)
    {
        $itemsWithDepths = array_fill_keys($urls, $depth);
        $this->items = array_merge($itemsWithDepths, $this->items);
    }

    public function all(int $depth = null)
    {
        return is_null($depth)
            ? array_keys($this->items)
            : array_keys($this->items, $depth);
    }


    public function next() : array
    {
        if (!$this->items) {
            return [];
        }


        $depth = reset($this->items);
        $url = key($this->items);
        unset($this->items[$url]);

        return [
            'url' => $url,
            'depth' => $depth,
        ];
    }


    public function count()
    {
        return count($this->items);
    }


}