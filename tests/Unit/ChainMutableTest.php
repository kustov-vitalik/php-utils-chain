<?php

namespace VKPHPUtils\Chain\Tests\Unit;


use PHPUnit\Framework\StaticAnalysis\HappyPath\AssertNotInstanceOf\A;
use PHPUnit\Framework\TestCase;
use VKPHPUtils\Chain\Chain;
use VKPHPUtils\Chain\Tests\Model\Address;
use VKPHPUtils\Chain\Tests\Model\Person;

class ChainMutableTest extends TestCase
{
    public function testToArray(): void
    {
        $GChain = Chain::of([1, 2, 3])->toArray();
        $this->assertIsArray($GChain);
        $this->assertCount(3, $GChain);

        $GChain = Chain::of([1, 2, 3, 4, 5]);
        $this->assertIsArray($GChain->toArray());
        $this->assertIsArray($GChain->toArray());
        $this->assertSame($GChain->toArray(), $GChain->toArray());
        $this->assertCount(5, $GChain->toArray());

        $GChain = Chain::of($this->gen(20));
        $this->assertIsArray($GChain->toArray());
        $this->assertIsArray($GChain->toArray());
        $this->assertSame($GChain->toArray(), $GChain->toArray());
        $this->assertCount(20, $GChain->toArray());

        $GChain = Chain::of($this->iterator(20));
        $this->assertIsArray($GChain->toArray());
        $this->assertIsArray($GChain->toArray());
        $this->assertSame($GChain->toArray(), $GChain->toArray());
        $this->assertCount(20, $GChain->toArray());
    }

    public function testOf(): void
    {
        $this->assertInstanceOf(Chain::class, Chain::of([1, 2, 3]));
        $this->assertInstanceOf(Chain::class, Chain::of($this->gen(5)));
        $this->assertInstanceOf(Chain::class, Chain::of($this->iterator(5)));
        $this->assertInstanceOf(Chain::class, Chain::of(new \ArrayObject([1, 2, 3])));
        $this->assertInstanceOf(Chain::class, Chain::of());
        $this->assertInstanceOf(Chain::class, Chain::of(1, 2, 3, 4, '5'));
        $this->assertInstanceOf(Chain::class, Chain::of(1, 2, 3, [4, 5], Chain::of(1, 3, 4)));
    }

    public function testMap(): void
    {
        $map = Chain::of([1, 2, 3, 4, 5])
            ->map(
                function ($value) {
                    return $value * 2;
                }
            );

        $this->assertInstanceOf(Chain::class, $map);


        $this->assertEquals([2, 4, 6, 8, 10], $map->toArray());
        $this->assertEquals(
            [1, 4, 9, 16, 25],
            $map->map(
                function ($v) {
                    return $v * $v;
                }
            )->toArray()
        );
    }

    public function testReverse(): void
    {
        $rev = Chain::of([1, 2, 3, 4, 5])
            ->reverse()
            ->toArray();

        $this->assertEquals([0, 1, 2, 3, 4], array_keys($rev));
        $this->assertEquals([5, 4, 3, 2, 1], array_values($rev));

        $rev = Chain::of([1, 2, 3, 4, 5])
            ->reverse(true)
            ->toArray();

        $this->assertEquals([4, 3, 2, 1, 0], array_keys($rev));
        $this->assertEquals([5, 4, 3, 2, 1], array_values($rev));

    }

    public function testHasKey(): void
    {
        $this->assertTrue(Chain::of(['a' => 1, 'b' => 2])->hasKey('a'));
        $this->assertTrue(Chain::of(['a' => 1, 'b' => 2])->hasKey('b'));
        $this->assertFalse(Chain::of(['a' => 1, 'b' => 2])->hasKey('c'));
    }

    public function testReduce(): void
    {
        $GChain = Chain::of(range(1, 10));
        $this->assertEquals(
            55,
            $GChain->reduce(
                function ($a, $b) {
                    return $a + $b;
                }
            )
        );
        $this->assertEquals(
            3628800,
            $GChain->reduce(
                function ($a, $b) {
                    return $a * $b;
                },
                1
            )
        );
    }

    public function testUnique(): void
    {
        $GChain = Chain::of([1, 1, 1, 3, 4, 5, 6, 6, 1, 2, 3, 4]);
        $this->assertSame([1, 3, 4, 5, 6, 2], $GChain->unique()->values()->toArray());
        $this->assertSame(range(0, 5), $GChain->unique()->keys()->toArray());
        $this->assertSame([0, 3, 4, 5, 6, 9], $GChain->unique(true)->keys()->toArray());
    }

    public function testToGChain(): void
    {
        $this->assertInstanceOf(Chain::class, Chain::of([1, 2, 3])->toChain());
        $this->assertInstanceOf(
            Chain::class,
            Chain::of([1, 2, 3])->map(
                function ($i) {
                    return $i * $i;
                }
            )->toChain()
        );
        $this->assertSame(
            [1, 4, 9],
            Chain::of([1, 2, 3])->map(
                function ($i) {
                    return $i * $i;
                }
            )->toChain()->toArray()
        );
    }

    public function testPrepend(): void
    {
        $this->assertSame([1], Chain::of([])->prepend(1)->toArray());
        $this->assertSame([2, 1], Chain::of()->prepend(1)->prepend(2)->toArray());
        $this->assertSame([3, 2, 1], Chain::of()->prepend(1)->prepend(2)->prepend(3)->toArray());
    }

    public function testHasValue(): void
    {
        $this->assertTrue(Chain::of([1, 2, 3])->hasValue(3));
        $this->assertFalse(Chain::of([1, 2, 3])->hasValue('3'));
        $this->assertFalse(Chain::of()->hasValue(3));
    }

    public function testMerge(): void
    {
        $this->assertSame([1, 2, 3, 4, 5], Chain::of([1, 2])->merge(Chain::of([3, 4, 5]))->toArray());
        $this->assertSame(
            [3, 4, 5, 1, 2],
            Chain::of([3])->merge(Chain::of([4, 5]))->merge(Chain::of([1, 2]))->toArray()
        );
    }

    public function testFlip(): void
    {
        $this->assertSame([1 => 'a', 2 => 'b', 5 => 'c'], Chain::of(['a' => 1, 'b' => 2, 'c' => 5])->flip()->toArray());
        $this->assertSame(
            ['ab' => 3, 'ac' => 4],
            Chain::of([1 => 'ab', 2 => 'ac', 3 => 'ab', 4 => 'ac'])->flip()->toArray()
        );
    }

    public function testKeys(): void
    {
        $this->assertSame([0, 1, 2], Chain::of([4, 5, 6])->keys()->toArray());
        $this->assertSame([4, 5, 6], Chain::of([4 => 'a', 5 => 'a', 6 => 'a'])->keys()->toArray());
    }

    public function testAppend(): void
    {
        $this->assertSame([1], Chain::of([])->append(1)->toArray());
        $this->assertSame([1, 2], Chain::of()->append(1)->append(2)->toArray());
        $this->assertSame([1, 2, 3], Chain::of()->append(1)->append(2)->append(3)->toArray());
    }

    public function testSortKeys(): void
    {
        $this->assertSame(
            ['a' => 23, 'b' => 10, 'c' => 'test'],
            Chain::of([10 => 'b', 23 => 'a', 'test' => 'c'])->flip()->sortKeys()->toArray()
        );
        $this->assertSame(
            ['c' => 'test', 'b' => 10, 'a' => 23],
            Chain::of([10 => 'b', 23 => 'a', 'test' => 'c'])->flip()->sortKeys(null, SORT_DESC)->toArray()
        );
        $this->assertSame(
            [23 => 'x', 100 => 'f', 101 => 'c'],
            Chain::of([100 => 'f', 23 => 'x', 'c'])->sortKeys(
                static function ($k1, $k2) {
                    return $k1 <=> $k2;
                }
            )->toArray()
        );
    }

    public function testSlice(): void
    {
        $this->assertSame([2, 4], Chain::of(range(0, 100))->slice(2, 5, 2)->toArray());
        $this->assertSame([], Chain::of(range(0, 100))->slice(101, 104)->toArray());
    }

    public function testSortValues(): void
    {
        $this->assertSame(['a', 'aa', 'b', 'c'], Chain::of(['b', 'c', 'aa', 'a'])->sortValues()->values()->toArray());
        $this->assertSame(
            array_reverse(['a', 'aa', 'b', 'c']),
            Chain::of(['b', 'c', 'aa', 'a'])->sortValues(null, SORT_DESC)->values()->toArray()
        );
        $this->assertSame(
            ['a', 'aa', 'b', 'c'],
            Chain::of(['b', 'c', 'aa', 'a'])->sortValues(
                function ($a1, $a2) {
                    return $a1 <=> $a2;
                }
            )->values()->toArray()
        );
        $this->assertSame(
            array_reverse(['a', 'aa', 'b', 'c']),
            Chain::of(['b', 'c', 'aa', 'a'])->sortValues(
                function ($a1, $a2) {
                    return $a2 <=> $a1;
                }
            )->values()->toArray()
        );
    }

    public function testFilter(): void
    {
        $o = new \stdClass();
        $this->assertSame(
            [0 => 'a', 1 => 1, 2 => 2, 3 => 3, 4 => $o, 5 => true],
            Chain::of(['a', 0, false, false, '', '', 1, 2, 3, $o, true])->filter()->values()->toArray()
        );
        $this->assertSame(
            [0 => 'a', 6 => 1, 7 => 2, 8 => 3, 9 => $o, 10 => true],
            Chain::of(['a', 0, false, false, '', '', 1, 2, 3, $o, true])->filter(null, true)->toArray()
        );
        $this->assertSame(
            [0 => 1, 1 => 2, 2 => 3],
            Chain::of(['a', 0, false, false, '', '', 1, 2, 3, $o, true])->filter(
                function ($a) {
                    return is_int($a) && $a > 0;
                }
            )->values()->toArray()
        );
        $this->assertSame(
            [6 => 1, 7 => 2, 8 => 3],
            Chain::of(['a', 0, false, false, '', '', 1, 2, 3, $o, true])->filter(
                function ($a) {
                    return is_int($a) && $a > 0;
                },
                true
            )->toArray()
        );
    }

    public function testForEach(): void
    {
        $person = new Person('name', 20, new Address('string', 10));

        Chain::of(range(1, 10))->forEach(
            function ($value, $index) use ($person) {
                $person->age += 1;
            }
        );

        $this->assertEquals(30, $person->age);
    }

    public function testGetIterator(): void
    {
        $this->assertInstanceOf(\Iterator::class, Chain::of()->getIterator());
        $this->assertInstanceOf(\Generator::class, Chain::of()->getIterator());
        $GChain = Chain::of([1, 2, 3]);
        foreach ($GChain as $index => $item) {
            $this->assertNotNull($index);
            $this->assertNotNull($index);
        }

    }

    public function testValues(): void
    {
        $GChain = Chain::of(['a' => 1, 'b' => 'c', 3 => 'd']);
        $this->assertSame([1, 'c', 'd'], $GChain->values()->toArray());
    }

    public function testIntersect(): void
    {
        $GChain = Chain::of([1, 2, 3, 4]);

        $this->assertSame([3, 4], $GChain->intersect(Chain::of([0, 3, 6, 9, 4, 5, 10]))->toArray());
        $this->assertSame([], $GChain->intersect(Chain::of([6, 7, 9]))->toArray());
        $this->assertSame($GChain->toArray(), $GChain->intersect($GChain)->toArray());
    }

    public function testIntersectKeepIndexes(): void
    {
        $GChain = Chain::of([1, 2, 3, 4]);

        $this->assertSame([2 => 3, 3 => 4], $GChain->intersectKeepIndexes(Chain::of([0, 3, 6, 9, 4, 5, 10]))->toArray());
        $this->assertSame([], $GChain->intersectKeepIndexes(Chain::of([6, 7, 9]))->toArray());
        $this->assertSame($GChain->toArray(), $GChain->intersectKeepIndexes($GChain)->toArray());
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue(Chain::of()->isEmpty());
        $this->assertFalse(Chain::of([1])->isEmpty());
    }

    public function testSize(): void
    {
        $this->assertEquals(0, Chain::of()->size());
        $this->assertEquals(1, Chain::of([1])->size());
        $this->assertEquals(5, Chain::of([1, 2, 3, 4, 5])->size());
        $chain = Chain::of(1,2,3,4,5);
        /** @var \Iterator $it */
        $it = $chain->getIterator();
        $it->next();
        $it->next();
        $this->assertEquals(5, $chain->size());
    }

    public function testSortByProperty(): void
    {
        $testData = [
            $a = ['name' => 'a', 'age' => 1, 'address' => ['street' => 'aa', 'home' => ['number' => 11]]],
            $b = ['name' => 'b', 'age' => 9, 'address' => ['street' => 'ab', 'home' => ['number' => 10]]],
            $c = ['name' => 'c', 'age' => 8, 'address' => ['street' => 'ac', 'home' => ['number' => 19]]],
            $d = ['name' => 'd', 'age' => 7, 'address' => ['street' => 'ba', 'home' => ['number' => 15]]],
            $e = ['name' => 'e', 'age' => 6, 'address' => ['street' => 'cd', 'home' => ['number' => 13]]],
            $f = ['name' => 'f', 'age' => 5, 'address' => ['street' => 'bf', 'home' => ['number' => 14]]],
        ];

        $GChain = Chain::of($testData);

        $this->assertSame(
            [0 => $a, 1 => $b, 2 => $c, 3 => $d, 4 => $e, 5 => $f],
            $GChain->sortByProperty('[name]')->toArray()
        );
        $this->assertSame(
            [1 => $b, 2 => $c, 3 => $d, 4 => $e, 5 => $f, 0 => $a],
            $GChain->sortByProperty('[age]', SORT_DESC)->toArray()
        );
        $this->assertSame(
            [4 => $e, 5 => $f, 3 => $d, 2 => $c, 1 => $b, 0 => $a],
            $GChain->sortByProperty('[address][street]', SORT_DESC)->toArray()
        );
        $this->assertSame(
            [1 => $b, 0 => $a, 4 => $e, 5 => $f, 3 => $d, 2 => $c],
            $GChain->sortByProperty('[address][home][number]')->toArray()
        );

        $testData = [
            $a = new Person('a', 1, new Address('aa', 11)),
            $b = new Person('b', 9, new Address('ab', 10)),
            $c = new Person('c', 8, new Address('ac', 19)),
            $d = new Person('d', 7, new Address('ba', 15)),
            $e = new Person('e', 6, new Address('cd', 13)),
            $f = new Person('f', 5, new Address('bf', 14)),
        ];

        $GChain = Chain::of($testData);

        $this->assertSame(
            [0 => $a, 1 => $b, 2 => $c, 3 => $d, 4 => $e, 5 => $f],
            $GChain->sortByProperty('name')->toArray()
        );
        $this->assertSame(
            [1 => $b, 2 => $c, 3 => $d, 4 => $e, 5 => $f, 0 => $a],
            $GChain->sortByProperty('age', SORT_DESC)->toArray()
        );
        $this->assertSame(
            [4 => $e, 5 => $f, 3 => $d, 2 => $c, 1 => $b, 0 => $a],
            $GChain->sortByProperty('address.street', SORT_DESC)->toArray()
        );
        $this->assertSame(
            [1 => $b, 0 => $a, 4 => $e, 5 => $f, 3 => $d, 2 => $c],
            $GChain->sortByProperty('address.home[number]')->toArray()
        );
    }

    public function testSortByPropertyInvalidProperty(): void
    {

        $testData = [
            $a = new Person('a', 1, new Address('aa', 11)),
            $b = new Person('b', 9, new Address('ab', 10)),
            $c = new Person('c', 8, new Address('ac', 19)),
            $d = new Person('d', 7, new Address('ba', 15)),
            $e = new Person('e', 6, new Address('cd', 13)),
            $f = new Person('f', 5, new Address('bf', 14)),
        ];

        $GChain = Chain::of($testData);
        $this->expectException(\InvalidArgumentException::class);
        $GChain->sortByProperty('invalidField')->toArray();
    }

    public function testSortByPropertyInvalidIndex(): void
    {

        $testData = [
            $a = new Person('a', 1, new Address('aa', 11)),
            $b = new Person('b', 9, new Address('ab', 10)),
            $c = new Person('c', 8, new Address('ac', 19)),
            $d = new Person('d', 7, new Address('ba', 15)),
            $e = new Person('e', 6, new Address('cd', 13)),
            $f = new Person('f', 5, new Address('bf', 14)),
        ];

        $GChain = Chain::of($testData);
        $this->expectException(\InvalidArgumentException::class);
        $GChain->sortByProperty('[invalidIndex]')->toArray();
    }

    public function testSortByPropertyInvalidSortDirection(): void
    {

        $testData = [
            $a = new Person('a', 1, new Address('aa', 11)),
            $b = new Person('b', 9, new Address('ab', 10)),
            $c = new Person('c', 8, new Address('ac', 19)),
            $d = new Person('d', 7, new Address('ba', 15)),
            $e = new Person('e', 6, new Address('cd', 13)),
            $f = new Person('f', 5, new Address('bf', 14)),
        ];

        $GChain = Chain::of($testData);
        $this->expectException(\InvalidArgumentException::class);
        $GChain->sortByProperty('name', 10)->toArray();
    }

    public function testFlatMap(): void
    {
        $testData = [
            $a = new Person('a', 1, new Address('aa', 11), ['Child11', 'Child12']),
            $b = new Person('b', 9, new Address('ab', 10), ['Child21', 'Child22']),
            $c = new Person('c', 8, new Address('ac', 19), ['Child31', 'Child32']),
            $d = new Person('d', 7, new Address('ba', 15), ['Child41', 'Child42']),
            $e = new Person('e', 6, new Address('cd', 13), ['Child51', 'Child52']),
            $f = new Person('f', 5, new Address('bf', 14)),
        ];

        $GChain = Chain::of($testData);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Passed to flatMap() callback function must return iterable data');
        $GChain->flatMap(
            static function (Person $person) {
                // the callback does not return iterable data
                return $person->getAddress();
            }
        )->toArray();

        $this->assertSame([
            $testData[0]->getAddress(),
            $testData[1]->getAddress(),
            $testData[2]->getAddress(),
            $testData[3]->getAddress(),
            $testData[4]->getAddress(),
            $testData[5]->getAddress(),
        ], $result = $GChain->flatMap(static function (Person $person) { yield $person->getAddress();})->toArray());

        $this->assertSame([
            $testData[0]->getAddress(),
            $testData[1]->getAddress(),
            $testData[2]->getAddress(),
            $testData[3]->getAddress(),
            $testData[4]->getAddress(),
            $testData[5]->getAddress(),
        ], $result = $GChain->flatMap(static function (Person $person) { return [$person->getAddress()];})->toArray());

        $this->assertSame([
            $testData[0]->getAddress(),
            $testData[1]->getAddress(),
            $testData[2]->getAddress(),
            $testData[3]->getAddress(),
            $testData[4]->getAddress(),
            $testData[5]->getAddress(),
        ], $result = $GChain->flatMap(static function (Person $person) { return new \ArrayIterator([$person->getAddress()]);})->toArray());

        $this->assertSame([
            'Child11', 'Child12', 'Child21', 'Child22', 'Child31', 'Child32', 'Child41', 'Child42', 'Child51', 'Child52'
        ], $GChain->flatMap(static function (Person $person) { yield from $person->getChildren(); })->toArray());

        $this->assertSame([
            'Child11', 'Child12', 'Child21', 'Child22', 'Child31', 'Child32', 'Child41', 'Child42', 'Child51', 'Child52'
        ], $GChain->flatMap(static function (Person $person) { return $person->getChildren(); })->toArray());

        $this->assertSame([
            'Child11', 'Child12', 'Child21', 'Child22', 'Child31', 'Child32', 'Child41', 'Child42', 'Child51', 'Child52'
        ], $GChain->flatMap(static function (Person $person) { return new \ArrayIterator($person->getChildren()); })->toArray());
    }

    public function testFrequencyAnalysis()
    {
        $this->assertSame(
            [
                1 => 5,
                2 => 4,
                3 => 3,
                4 => 2,
                5 => 1,
                6 => 1,
            ],
            Chain::of([1, 2, 3, 4, 5, 6, 1, 2, 3, 4, 1, 2, 3, 1, 2, 1])->frequencyAnalysis()->toArray()
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->assertSame(
            [
                1 => 5,
                2 => 4,
                3 => 3,
                4 => 2,
                5 => 1,
                6 => 1,
            ],
            Chain::of([1, 2, 3, 4, 5, 6, 1, 2, 3, 4, 1, 2, 3, 1, 2, []])->frequencyAnalysis()->toArray()
        );
    }

    public function testDiff(): void
    {
        $this->assertSame(
            [1 => 0, 2 => 1, 3 => 2],
            Chain::of([-1, 0, 1, 2, 3, 4])->diff([3, 4, -1, 5, 6])->toArray()
        );
        $this->assertSame([2 => 5, 3 => 6], Chain::of([3, 4, 5, 6])->diff([1, 2, 3, 4])->toArray());


        $o1 = new \stdClass();
        $o2 = new \stdClass();
        $o3 = new \stdClass();

        $chain1 = Chain::of([$o1, $o2, $o3]);
        $chain2 = Chain::of([$o1]);

        $this->assertSame([1 => $o2, 2 => $o3], $chain1->diff($chain2)->toArray());
        $this->assertSame([], $chain2->diff($chain1)->toArray());

        $chain3 = Chain::of([1, $o1, $o2]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not compare object with scalar');
        $chain3->diff($chain1)->toArray();
    }

    public function testMix(): void
    {
        $chain = Chain::of(1, 2, 3, 4)->mix();
        $this->assertTrue($chain->hasValue(1));
        $this->assertTrue($chain->hasValue(2));
        $this->assertTrue($chain->hasValue(3));
        $this->assertTrue($chain->hasValue(4));
    }

    public function testSetValue(): void
    {
        $chain = Chain::of();
        $this->assertSame(['key' => 'value'], $chain->setValue('key', 'value')->toArray());
        $this->assertSame(['key' => 'value2'], $chain->setValue('key', 'value')->setValue('key', 'value2')->toArray());
        $this->assertSame(
            ['key' => 'value2', 'k2' => 0],
            $chain->setValue('key', 'value')->setValue('key', 'value2')->setValue('k2', 0)->toArray()
        );

    }

    public function testGetValue()
    {
        $chain = Chain::of(0,1,2,3,4,5,6);
        $this->assertEquals(0, $chain->getValue(0));
        $this->assertEquals(1, $chain->getValue(1));
        $this->assertEquals(2, $chain->getValue(2));
        $this->assertEquals(3, $chain->getValue(3));
        $this->assertEquals(4, $chain->getValue(4));
        $this->assertEquals(5, $chain->getValue(5));
        $this->assertEquals(6, $chain->getValue(6));
        $this->assertNull($chain->getValue(7));
    }

    public function testSearch(): void
    {
        $chain = Chain::of(0,6);
        $this->assertEquals(0, $chain->search(0));
        $this->assertEquals(1, $chain->search(6));
        $this->assertNull($chain->search('6'));
        $this->assertNull($chain->getValue(7));
    }

    private function gen(int $i): \Generator {
        foreach (range(0, $i - 1) as $index => $item) {
            yield $index => $item;
        }
    }

    private function iterator(int $i): \Iterator {
        return new \ArrayIterator(range(0, $i - 1));
    }

    public function testCount(): void
    {
        $chain = Chain::of(0,6);
        $this->assertCount(2, $chain);
        $this->assertEquals(2, count($chain));
    }

    public function testRemove(): void
    {
        $chain = Chain::of('test', 'test2');
        $chain = $chain->remove(0);
        $this->assertFalse($chain->hasKey(0));
        $this->assertTrue($chain->hasKey(1));
    }

    public function testJsonSerialize(): void
    {
        $this->assertEquals(json_encode([0 => 1, 1 => 2]), json_encode(Chain::of(1, 2)));
        $this->assertEquals(
            json_encode([0 => 1, 1 => 2], JSON_THROW_ON_ERROR, 512),
            json_encode(Chain::of(1, 2), JSON_THROW_ON_ERROR, 512)
        );
    }
}
