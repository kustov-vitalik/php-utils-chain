<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain\Tests\Functional;


use PHPUnit\Framework\TestCase;
use VKPHPUtils\Chain\Chain;

class MutableChainsTest extends TestCase
{
    public function testMutability(): void
    {
        $chain = Chain::of([['name' => 1], ['name' => 2], ['name' => 3]]);
        $this->assertSame($chain, $chain->sortValues(static function ($a, $b) { return $a['name'] <=> $b['name'];}));
        $this->assertSame($chain, $chain->sortKeys());
        $this->assertSame($chain, $chain->sortByProperty('[name]'));
        $this->assertSame($chain, $chain->map(static function ($item) {return $item['name'] * $item['name'];}));
        $this->assertSame($chain, $chain->flip());
        $this->assertSame($chain, $chain->filter());
        $this->assertSame($chain, $chain->intersectKeepIndexes([1,2,3]));
        $this->assertSame($chain, $chain->intersect([1,2,3]));
        $this->assertSame($chain, $chain->flatMap(static function ($i) {return [$i];}));
        $this->assertSame($chain, $chain->merge(1));
        $this->assertSame($chain, $chain->unique());
        $this->assertSame($chain, $chain->forEach(static function ($i) {}));
        $this->assertSame($chain, $chain->values());
        $this->assertSame($chain, $chain->keys());
        $this->assertSame($chain, $chain->slice(0,2));
        $this->assertSame($chain, $chain->append(1));
        $this->assertSame($chain, $chain->prepend(1));
        $this->assertSame($chain, $chain->reverse());
        $this->assertNotSame($chain, $chain->toChain());
    }
}