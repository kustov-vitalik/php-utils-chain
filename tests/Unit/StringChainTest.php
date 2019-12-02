<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain\Tests\Unit;


use PHPUnit\Framework\TestCase;
use VKPHPUtils\Chain\Chain;
use VKPHPUtils\Chain\StringChain;

class StringChainTest extends TestCase
{
    public function testRandom(): void
    {
        $this->assertEquals(6, strlen((string)StringChain::random()));
        $this->assertEquals(10, strlen((string)StringChain::random(10)));
        $this->assertEquals(1000, strlen((string)StringChain::random(1000)));
        $this->assertEquals(0, strlen((string)StringChain::random(0)));
        $this->assertEquals(0, strlen((string)StringChain::random(-1)));
    }

    public function testToUpperCase(): void
    {
        $this->assertEquals('STRING', StringChain::of('string')->toUpperCase());
        $this->assertEquals('STRING_STRING', StringChain::of('string_STRING')->toUpperCase());
        $this->assertEquals('STRINGSTRING', StringChain::of('stringString')->toUpperCase());
    }

    public function testToLowerCase(): void
    {
        $this->assertEquals('string', StringChain::of('STRING')->toLowerCase());
        $this->assertEquals('string_string', StringChain::of('string_STRING')->toLowerCase());
        $this->assertEquals('stringstring', StringChain::of('StringString')->toLowerCase());
    }

    public function testToPascalCase(): void
    {
        $this->assertEquals('String', StringChain::of('string')->toPascalCase());
        $this->assertEquals('StringSTRING', StringChain::of('string_STRING')->toPascalCase());
        $this->assertEquals('StringString', StringChain::of('String_string')->toPascalCase());
    }

    public function testToCamelCase(): void
    {
        $this->assertEquals('string', StringChain::of('string')->toCamelCase());
        $this->assertEquals('stringSTRING', StringChain::of('string_STRING')->toCamelCase());
        $this->assertEquals('stringString', StringChain::of('String_string')->toCamelCase());
    }

    public function testToUnderScoreCase(): void
    {
        $this->assertEquals('string', StringChain::of('string')->toUnderScoreCase());
        $this->assertEquals('string_string', StringChain::of('stringSTRING')->toUnderScoreCase());
        $this->assertEquals('string_string', StringChain::of('StringString')->toUnderScoreCase());
    }

    public function testCapitalize(): void
    {
        $this->assertEquals('String', StringChain::of('string')->capitalize());
        $this->assertEquals('Stringstring', StringChain::of('stringSTRING')->capitalize());
        $this->assertEquals('Stringstring', StringChain::of('StringString')->capitalize());
    }

    public function testToChain(): void
    {
        $this->assertInstanceOf(Chain::class, StringChain::of('string string')->toChain(''));
        $this->assertCount(13, StringChain::of('string string')->toChain(''));
        $this->assertInstanceOf(Chain::class, StringChain::of('string string')->toChain(' '));
        $this->assertCount(2, StringChain::of('string string')->toChain(' '));
    }

    public function testTrim(): void
    {
        $this->assertEquals('string', StringChain::of('  string  ')->trim());
    }

    public function testTrimLeft(): void
    {
        $this->assertEquals('string  ', StringChain::of('  string  ')->trimLeft());
    }

    public function testTrimRight(): void
    {
        $this->assertEquals('  string', StringChain::of('  string  ')->trimRight());
    }

    public function testSubString(): void
    {
        $this->assertEquals('string', StringChain::of('string  ')->subString(0, 6));
        $this->assertEquals('string  ', StringChain::of('string  ')->subString(0));
        $this->assertEquals('tr', StringChain::of('string  ')->subString(1, 2));
        $this->assertEquals('', StringChain::of('string  ')->subString(100, 100));
        $this->assertEquals(' ', StringChain::of('string  ')->subString(-1, 2));
        $this->assertEquals(' ', StringChain::of('string  ')->subString(-1, 3));
        $this->assertEquals(' ', StringChain::of('string  ')->subString(-1, 300));
        $this->assertEquals('  ', StringChain::of('string  ')->subString(-2, 2));
        $this->assertEquals('  ', StringChain::of('string  ')->subString(-2, 3));
        $this->assertEquals('  ', StringChain::of('string  ')->subString(-2, 300));
        $this->assertEquals('g ', StringChain::of('string  ')->subString(-3, 2));
        $this->assertEquals('g  ', StringChain::of('string  ')->subString(-3, 3));
        $this->assertEquals('g  ', StringChain::of('string  ')->subString(-3, 300));
    }

    public function testIndexOf(): void
    {
        $this->assertEquals(1, StringChain::of('string')->indexOf('t'));
        $this->assertEquals(-1, StringChain::of('string')->indexOf('b'));
    }

    public function testSize(): void
    {
        $this->assertEquals(6, StringChain::of('string')->size());
    }

    public function testCharAt(): void
    {
        $this->assertEquals('s', StringChain::of('string')->charAt(0));
        $this->assertEquals('t', StringChain::of('string')->charAt(1));
        $this->assertEquals('r', StringChain::of('string')->charAt(2));
        $this->assertEquals('i', StringChain::of('string')->charAt(3));
        $this->assertEquals('n', StringChain::of('string')->charAt(4));
        $this->assertEquals('g', StringChain::of('string')->charAt(5));
        $this->assertEquals('', StringChain::of('string')->charAt(6));
        $this->assertEquals('g', StringChain::of('string')->charAt(-1));
        $this->assertEquals('n', StringChain::of('string')->charAt(-2));
        $this->assertEquals('i', StringChain::of('string')->charAt(-3));
        $this->assertEquals('r', StringChain::of('string')->charAt(-4));
        $this->assertEquals('t', StringChain::of('string')->charAt(-5));
        $this->assertEquals('s', StringChain::of('string')->charAt(-6));
        $this->assertEquals('', StringChain::of('string')->charAt(-7));
    }
}