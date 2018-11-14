<?php


namespace Radowoj\Crawla\Tests\Link;


use PHPUnit\Framework\TestCase;
use Radowoj\Crawla\Link\Collection;

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

}
