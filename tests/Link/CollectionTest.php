<?php


namespace Radowoj\Crawla\Tests\Link;


use PHPUnit\Framework\TestCase;
use Radowoj\Crawla\Link\Collection;
use Radowoj\Crawla\Link\Link;

class CollectionTest extends TestCase
{

    public function testCreateInstance()
    {
        $collection = new Collection();
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEmpty($collection->toArray());
    }


    public function testCreateFromArray()
    {
        $array = [
            'https://github.com' => 0
        ];
        $collection = new Collection($array);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame($array, $collection->toArray());
    }


    /**
     * @expectedException \Radowoj\Crawla\Exception\InvalidUrlException
     */
    public function testCreateFromArrayFailsOnInvalidUrl()
    {
        new Collection([
            'definately not an url address' => 0
        ]);
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromArrayFailsOnNonStringUrl()
    {
        new Collection([
            0 => 0
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromArrayFailsOnNonIntDepth()
    {
        new Collection([
            'https://github.com' => 1.2
        ]);
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromArrayFailsOnNegativeDepth()
    {
        new Collection([
            'https://github.com' => -1
        ]);
    }

    public function testPush()
    {
        $link0 = new Link('https://github.com/radowoj/crawla', 0);
        $collection = new Collection([
            $link0->getUrl() => $link0->getDepth()
        ]);

        $link1 = new Link('https://github.com', 1);
        $link2 = new Link('https://github.com/radowoj', 2);

        $collection->push($link1);
        $collection->push($link2);

        $this->assertArraySubset([$link0->getUrl() => $link0->getDepth()], $collection->toArray());
        $this->assertArraySubset([$link1->getUrl() => $link1->getDepth()], $collection->toArray());
        $this->assertArraySubset([$link2->getUrl() => $link2->getDepth()], $collection->toArray());
    }

}
