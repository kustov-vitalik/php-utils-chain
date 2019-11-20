<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain\Tests\Unit;


use PHPUnit\Framework\TestCase;
use VKPHPUtils\Chain\Generator;

class GeneratorTest extends TestCase
{
    public function testGenerator(): void
    {
        $gen = static function (int $i) {
            foreach (range(0, $i - 1) as $item) {
                yield $item;
            }
        };

        $generator = new Generator($gen(10));

        $result = null;
        $counter = 0;
        foreach ($generator as $k => $value) {
            $result[$k] = $value;
            $counter++;
            if ($counter === 1) {
                break;
            }
        }

        $this->assertSame([0], $result);

        $result = null;
        $counter = 0;
        foreach ($generator as $k => $value) {
            $result[$k] = $value;
            $counter++;
            if ($counter === 5) {
                break;
            }
        }
        $this->assertSame([0,1,2,3,4], $result);

        $result = null;
        $counter = 0;
        foreach ($generator as $k => $value) {
            $result[$k] = $value;
            $counter++;
            if ($counter === 10) {
                break;
            }
        }
        $this->assertSame([0,1,2,3,4,5,6,7,8,9], $result);

        $result = null;
        $counter = 0;
        foreach ($generator as $k => $value) {
            $result[$k] = $value;
            $counter++;
            if ($counter === 20) {
                break;
            }
        }
        $this->assertSame([0,1,2,3,4,5,6,7,8,9], $result);


        $it = static function(int $i) {
            return new \ArrayIterator(range(0, $i - 1));
        };

        $generator = new Generator($it(10));

        $result = null;
        $counter = 0;
        foreach ($generator as $k => $value) {
            $result[$k] = $value;
            $counter++;
            if ($counter === 1) {
                break;
            }
        }

        $this->assertSame([0], $result);

        $result = null;
        $counter = 0;
        foreach ($generator as $k => $value) {
            $result[$k] = $value;
            $counter++;
            if ($counter === 5) {
                break;
            }
        }
        $this->assertSame([0,1,2,3,4], $result);

        $result = null;
        $counter = 0;
        foreach ($generator as $k => $value) {
            $result[$k] = $value;
            $counter++;
            if ($counter === 10) {
                break;
            }
        }
        $this->assertSame([0,1,2,3,4,5,6,7,8,9], $result);

        $result = null;
        $counter = 0;
        foreach ($generator as $k => $value) {
            $result[$k] = $value;
            $counter++;
            if ($counter === 20) {
                break;
            }
        }
        $this->assertSame([0,1,2,3,4,5,6,7,8,9], $result);

        $generator = new Generator(range(0, 9));

        $result = null;
        $counter = 0;
        foreach ($generator as $k => $value) {
            $result[$k] = $value;
            $counter++;
            if ($counter === 1) {
                break;
            }
        }

        $this->assertSame([0], $result);

        $result = null;
        $counter = 0;
        foreach ($generator as $k => $value) {
            $result[$k] = $value;
            $counter++;
            if ($counter === 5) {
                break;
            }
        }
        $this->assertSame([0,1,2,3,4], $result);

        $result = null;
        $counter = 0;
        foreach ($generator as $k => $value) {
            $result[$k] = $value;
            $counter++;
            if ($counter === 10) {
                break;
            }
        }
        $this->assertSame([0,1,2,3,4,5,6,7,8,9], $result);

        $result = null;
        $counter = 0;
        foreach ($generator as $k => $value) {
            $result[$k] = $value;
            $counter++;
            if ($counter === 20) {
                break;
            }
        }
        $this->assertSame([0,1,2,3,4,5,6,7,8,9], $result);
    }
}