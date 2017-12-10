<?php
/**
 * @author Radosław Wojtyczka <radoslaw.wojtyczka@gmail.com>
 */

namespace Radowoj\Crawla\Tests\Link;


use PHPUnit\Framework\TestCase;
use Radowoj\Crawla\Link\Collection;
use Radowoj\Crawla\Link\CollectionInterface;

class CollectionTest extends TestCase
{

    /**
     * @var CollectionInterface;
     */
    protected $collection = null;


    public function setUp()
    {
        $this->collection = new Collection();
    }


    public function testIsEmptyOnCreation()
    {
        $this->assertEmpty($this->collection->all(), 'Return value of all() is not empty upon creation');
        $this->assertEmpty($this->collection->toArray(), 'Return value of toArray() is not empty upon creation');
        $this->assertEmpty($this->collection->toAssoc(), 'Return value of toAssoc() is not empty upon creation');
    }


    public function testPush()
    {
        $this->collection->push('http://www.github.com', 10);
        $this->assertEquals(['http://www.github.com' => 10], $this->collection->toArray(), 'Pushed element is not present in returned array');
        $this->assertEquals(1, $this->collection->count(), 'Count is invalid after pushing 1 element');
        $this->assertEquals(['http://www.github.com'], $this->collection->all());
    }


    public function testPushDefaultDepth()
    {
        $this->collection->push('http://www.github.com');
        $this->assertEquals(['http://www.github.com' => 0], $this->collection->toArray(), 'Pushed element is not present in returned array');
        $this->assertEquals(1, $this->collection->count(), 'Count is invalid after pushing 1 element');
        $this->assertEquals(['http://www.github.com'], $this->collection->all());
    }


    public function testAppend()
    {
        $this->collection->append(['http://www.github.com', 'http://www.bitbucket.org'], 10);
        $this->assertEquals([
            'http://www.github.com' => 10,
            'http://www.bitbucket.org' => 10,
        ], $this->collection->toArray());
        $this->assertEquals([
           'http://www.github.com',
           'http://www.bitbucket.org'
        ], $this->collection->all());
    }


    public function testAllFromDepth()
    {
        $this->collection->push('http://www.github.com', 10);
        $this->collection->push('http://www.bitbucket.org', 12);

        $this->assertEquals(['http://www.github.com'], $this->collection->all(10));
        $this->assertEquals(['http://www.bitbucket.org'], $this->collection->all(12));
    }


    public function testShift()
    {
        $this->collection->push('http://www.github.com', 10);
        $this->collection->push('http://www.bitbucket.org', 12);

        $this->assertEquals(['http://www.github.com'], $this->collection->all(10));
        $this->assertEquals(['http://www.bitbucket.org'], $this->collection->all(12));

        $element = $this->collection->shift();
        $this->assertEquals([
            'url' => 'http://www.github.com',
            'depth' => 10,
        ], $element);

        $element = $this->collection->shift();
        $this->assertEquals([
            'url' => 'http://www.bitbucket.org',
            'depth' => 12,
        ], $element);

        $this->assertEmpty($this->collection->shift());
    }


    public function testPushElement()
    {
        $this->collection->pushElement([
            'url' => 'http://www.github.com',
            'depth' => 123
        ]);
        $this->assertEquals([
            'http://www.github.com' => 123
        ], $this->collection->toArray());
    }


    public function testFromAndToArray()
    {
        $array = ['http://www.github.com' => 2, 'http://www.bitbucket.org' => 3];
        $this->collection->fromArray($array);
        $this->assertEquals($array, $this->collection->toArray());
    }


    public function testFromAndToAssoc()
    {
        $assoc = [
            [
                'url' => 'http://www.github.com',
                'depth' => 2
            ],
            [
                'url' => 'http://www.bitbucket.org',
                'depth' => 3,
            ]
        ];

        $this->collection->fromAssoc($assoc);
        $this->assertEquals($assoc, $this->collection->toAssoc());
    }


    public function testCollectionIsEmptiedOnFromArrayOrAssoc()
    {
        $this->collection->push('http://www.github.com');
        $this->assertEquals(1, $this->collection->count());
        $this->collection->fromAssoc([]);
        $this->assertEquals(0, $this->collection->count(), 'fromAssoc should empty the collection first');

        $this->collection->push('http://www.github.com');
        $this->assertEquals(1, $this->collection->count());
        $this->collection->fromArray([]);
        $this->assertEquals(0, $this->collection->count(), 'fromArray should empty the collection first');

    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionOnPushedElementMissingUrl()
    {
        $this->collection->pushElement(['depth' => 1]);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionOnPushedElementMissingDepth()
    {
        $this->collection->pushElement(['url' => 1]);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionOnPushedElementsUrlNotBeingString()
    {
        $this->collection->pushElement(['url' => -1]);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionOnPushedElementsDepthNotBeingInt()
    {
        $this->collection->pushElement(['depth' => 'one']);
    }



}