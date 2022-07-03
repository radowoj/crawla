<?php

declare(strict_types=1);

namespace Radowoj\Crawla\Tests\Link;

use PHPUnit\Framework\TestCase;
use Radowoj\Crawla\Exception\InvalidUrlException;
use Radowoj\Crawla\Link\Collection;
use Radowoj\Crawla\Link\Link;

class CollectionTest extends TestCase
{
    public function testCreateInstance(): void
    {
        $collection = new Collection();
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEmpty($collection->toArray());
    }

    public function testCreateFromArray(): void
    {
        $array = [
            'https://github.com' => 0,
        ];
        $collection = new Collection($array);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame($array, $collection->toArray());
    }

    public function testCreateFromArrayFailsOnInvalidUrl(): void
    {
        $this->expectException(InvalidUrlException::class);
        new Collection([
            'definately not an url address' => 0,
        ]);
    }

    public function testCreateFromArrayFailsOnNonStringUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Collection([
            0 => 0,
        ]);
    }

    public function testCreateFromArrayFailsOnNonIntDepth(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Collection([
            'https://github.com' => 1.2,
        ]);
    }

    public function testCreateFromArrayFailsOnNegativeDepth(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Collection([
            'https://github.com' => -1,
        ]);
    }

    public function testPushingSingleLinkToCollection(): void
    {
        $link0 = new Link('https://github.com', 0);
        $link1 = new Link('https://github.com/radowoj', 1);
        $link2 = new Link('https://github.com/radowoj/crawla', 2);

        $collection = new Collection([
            $link0->getUrl() => $link0->getDepth(),
        ]);

        $collection->push($link1);
        $collection->push($link2);

        $this->assertArraySubset([$link0->getUrl() => $link0->getDepth()], $collection->toArray());
        $this->assertArraySubset([$link1->getUrl() => $link1->getDepth()], $collection->toArray());
        $this->assertArraySubset([$link2->getUrl() => $link2->getDepth()], $collection->toArray());
    }

    public function testAppendingMultipleUrlsAtGivenDepth(): void
    {
        $link0 = new Link('https://github.com', 0);
        $link1 = new Link('https://github.com/radowoj', 1);
        $link2 = new Link('https://github.com/radowoj/crawla', 1);
        $link3 = new Link('https://github.com/radowoj/crawla/commits/master', 2);

        $collection = new Collection([$link0->getUrl() => $link0->getDepth()]);

        $collection->appendUrlsAtDepth([$link1->getUrl(), $link2->getUrl()], $link1->getDepth());
        $collection->appendUrlsAtDepth([$link3->getUrl()], $link3->getDepth());

        $this->assertArraySubset([$link0->getUrl() => $link0->getDepth()], $collection->toArray());
        $this->assertArraySubset([$link1->getUrl() => $link1->getDepth()], $collection->toArray());
        $this->assertArraySubset([$link2->getUrl() => $link2->getDepth()], $collection->toArray());
        $this->assertArraySubset([$link3->getUrl() => $link3->getDepth()], $collection->toArray());
    }

    public function testGettingAllLinks(): void
    {
        $linkAtDepth0 = new Link('https://github.com', 0);
        $linkAtDepth1 = new Link('https://github.com/radowoj', 1);

        $collection = new Collection();
        $collection->push($linkAtDepth0);
        $collection->push($linkAtDepth1);

        $this->assertCount(2, $collection->all());
        $this->assertContains($linkAtDepth0->getUrl(), $collection->all());
        $this->assertContains($linkAtDepth1->getUrl(), $collection->all());

        $this->assertCount(1, $collection->all($linkAtDepth0->getDepth()));
        $this->assertContains($linkAtDepth0->getUrl(), $collection->all($linkAtDepth0->getDepth()));

        $this->assertCount(1, $collection->all($linkAtDepth1->getDepth()));
        $this->assertContains($linkAtDepth1->getUrl(), $collection->all($linkAtDepth1->getDepth()));
    }

    public function testShiftFirstElementFromCollection(): void
    {
        $linkAtDepth0 = new Link('https://github.com', 0);
        $linkAtDepth1 = new Link('https://github.com/radowoj', 1);

        $collection = new Collection();
        $collection->push($linkAtDepth0);
        $collection->push($linkAtDepth1);

        $this->assertEquals($linkAtDepth0, $collection->shift());
        $this->assertEquals($linkAtDepth1, $collection->shift());
        $this->assertNull($collection->shift());
    }

    public function testCount(): void
    {
        $linkAtDepth0 = new Link('https://github.com', 0);
        $linkAtDepth1 = new Link('https://github.com/radowoj', 1);

        $collection = new Collection();
        $collection->push($linkAtDepth0);
        $this->assertSame(1, $collection->count());

        $collection->push($linkAtDepth1);
        $this->assertSame(2, $collection->count());
    }

    public function assertArraySubset(array $needle, array $haystack): void
    {
        $keys = array_keys($needle);
        foreach($keys as $key) {
            $this->assertArrayHasKey($key, $haystack);
            $this->assertSame($needle[$key], $haystack[$key]);
        }

    }
}
