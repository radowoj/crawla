<?php

declare(strict_types=1);

namespace Radowoj\Crawla\Tests\Link;

use PHPUnit\Framework\TestCase;
use Radowoj\Crawla\Exception\InvalidUrlException;
use Radowoj\Crawla\Link\Link;

class LinkTest extends TestCase
{
    const TEST_URL = 'https://github.com';
    const TEST_DEFAULT_DEPTH = 0;
    const TEST_DEPTH = 123;

    public function testCreateNewLinkWithDefaultDepth()
    {
        $link = new Link(self::TEST_URL);
        $this->assertInstanceOf(Link::class, $link);
        $this->assertSame(self::TEST_DEFAULT_DEPTH, $link->getDepth());
        $this->assertSame(self::TEST_URL, $link->getUrl());
    }

    public function testCreateNewLinkWithCustomDepth()
    {
        $link = new Link(self::TEST_URL, self::TEST_DEPTH);
        $this->assertInstanceOf(Link::class, $link);
        $this->assertSame(self::TEST_DEPTH, $link->getDepth());
        $this->assertSame(self::TEST_URL, $link->getUrl());
    }

    public function testUrlValidation()
    {
        $this->expectException(InvalidUrlException::class);
        new Link('definately not an url address');
    }

    public function testDepthMustBeNonNegative()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Link(self::TEST_URL, -1);
    }
}
