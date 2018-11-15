<?php

declare(strict_types=1);

namespace Radowoj\Crawla\Link;

use Radowoj\Crawla\Exception\InvalidUrlException;

class Link
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $depth;

    /**
     * Link constructor.
     *
     * @param string $url
     * @param int    $depth
     */
    public function __construct(string $url, int $depth = 0)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException("Provided URL is invalid: {$url}");
        }

        if ($depth < 0) {
            throw new \InvalidArgumentException('Provided depth must be non negative');
        }
        $this->url = $url;
        $this->depth = $depth;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }
}
