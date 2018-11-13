<?php

declare(strict_types=1);

namespace Radowoj\Crawla\Link;

interface CollectionInterface
{
    public function push(string $url, int $depth = 0): self;

    public function append(array $urls, int $depth = 0): self;

    public function pushElement(array $element);

    public function all();

    public function shift(): array;

    public function count();

    public function toArray();

    public function fromArray(array $sourceArray);

    public function toAssoc();

    public function fromAssoc(array $sourceAssoc);
}
