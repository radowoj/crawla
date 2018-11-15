<?php

declare(strict_types=1);

namespace Radowoj\Crawla\Link;

interface CollectionInterface
{
    public function push(Link $link): self;

    public function appendUrlsAtDepth(array $urls, int $depth = 0): self;

    public function all();

    public function shift(): ?Link;

    public function count();

    public function toArray();
}
