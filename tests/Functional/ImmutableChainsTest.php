<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain\Tests\Functional;


use PHPUnit\Framework\TestCase;
use VKPHPUtils\Chain\Chain;

class ImmutableChainsTest extends TestCase
{
    public function testImmutability(): void
    {
        $chain = Chain::immutable([['name' => 1], ['name' => 2], ['name' => 3]]);
        $this->assertNotSame($chain, $chain->map(static function ($item) {return $item * $item;}));
        $this->assertNotSame($chain, $chain->flip());
        $this->assertNotSame($chain, $chain->filter());
        $this->assertNotSame($chain, $chain->intersectKeepIndexes([1,2,3]));
        $this->assertNotSame($chain, $chain->intersect([1,2,3]));
        $this->assertNotSame($chain, $chain->flatMap(static function ($i) {return [$i];}));
        $this->assertNotSame($chain, $chain->merge(1));
        $this->assertNotSame($chain, $chain->unique());
        $this->assertNotSame($chain, $chain->forEach(static function ($i) {}));
        $this->assertNotSame($chain, $chain->values());
        $this->assertNotSame($chain, $chain->keys());
        $this->assertNotSame($chain, $chain->slice(1,2));
        $this->assertNotSame($chain, $chain->append(1));
        $this->assertNotSame($chain, $chain->prepend(1));
        $this->assertNotSame($chain, $chain->reverse());
        $this->assertNotSame($chain, $chain->sortByProperty('[name]'));
        $this->assertNotSame($chain, $chain->sortValues(static function ($a, $b) { return $a['name'] <=> $b['name'];}));
        $this->assertNotSame($chain, $chain->sortKeys());
        $this->assertNotSame($chain, $chain->toChain());
    }
}