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

    public function all()
    {
        return array_keys($this->items);
    }


    public function fromDepth(int $depth)
    {
        return array_keys($this->items, $depth);
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