<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain;


use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class Chain
 * @package VKPHPUtils\Chain
 */
abstract class Chain implements \IteratorAggregate, \JsonSerializable, \Countable
{

    protected Generator $generator;

    /**
     * Initialize mutable chain
     * @param mixed ...$items
     * @return Chain
     */
    public static function of(...$items): Chain
    {
        return ChainMutable::of(...$items);
    }

    /**
     * Initialize immutable chain
     * @param mixed ...$items
     * @return Chain
     */
    public static function immutable(...$items): Chain
    {
        return ChainImmutable::of(...$items);
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Converts the chain to array (applies all chain-functions)
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * Same as {@see size()}. Allows to call count($chain)
     * @return int
     */
    public function count(): int
    {
        return $this->size();
    }

    /**
     * Get the chain size (number of elements)
     * @return int
     */
    public function size(): int
    {
        return iterator_count($this->generator);
    }

    /**
     * Searches position (key) of the value in current chain. Returns null if the value not found
     * @param $value
     * @return mixed
     */
    public function search($value)
    {
        foreach ($this as $k => $item) {
            if ($value === $item) {
                return $k;
            }
        }

        return null;
    }

    /**
     * Get value using key
     * @param $key
     * @return mixed
     */
    public function getValue($key)
    {
        foreach ($this as $k => $item) {
            if ($k === $key) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Check exists the key in current chain or no
     * @param $key
     * @return bool
     */
    public function hasKey($key): bool
    {
        foreach ($this as $index => $v) {
            if ($index === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate aggregate function result
     * @param callable $fn
     * @param mixed $initialValue
     * @return mixed
     */
    public function reduce(callable $fn, $initialValue = null)
    {
        $result = $initialValue;
        foreach ($this as $value) {
            $result = $fn($result, $value);
        }

        return $result;
    }

    /**
     * Check is the chain empty
     * @return bool
     */
    public function isEmpty(): bool
    {
        foreach ($this as $item) {
            return false;
        }

        return true;
    }

    /**
     * Created histogram/frequency analysis of the chain
     * @return Chain
     */
    abstract public function frequencyAnalysis(): Chain;

    /**
     * Calculates diff between current chain and passed one
     * @param mixed ...$elements
     * @return Chain
     */
    abstract public function diff(...$elements): Chain;

    /**
     * Mixes the chain
     * @return Chain
     */
    abstract public function mix(): Chain;

    /**
     * Set value using key
     * @param $key
     * @param mixed $value
     * @return Chain
     */
    abstract public function setValue($key, $value): Chain;

    /**
     * Removes element with index eq to $key
     * @param $key
     * @return Chain
     */
    abstract public function remove($key): Chain;

    /**
     * Flip the chain
     * @return Chain
     */
    abstract public function flip(): Chain;

    /**
     * Applies function $fn to each chain item and returns chain with mapped entities. Index-safe operation
     * @param callable $fn
     * @return Chain
     */
    abstract public function map(callable $fn): Chain;

    /**
     * Reverse current chain
     * @param bool $saveIndex
     * @return Chain
     */
    abstract public function reverse(bool $saveIndex = false): Chain;

    /**
     * Unique items of current chain
     * @param bool $saveIndex
     * @return Chain
     */
    abstract public function unique(bool $saveIndex = false): Chain;

    /**
     * Useless operation with immutable chain. Does nothing useful.
     * Useful with mutable chain. Returns new chain with all chain-functions applied
     * @return Chain
     */
    abstract public function toChain(): Chain;

    /**
     * Prepend value in the chain
     * @param $any
     * @return Chain
     */
    abstract public function prepend($any): Chain;

    /**
     * Check existence of the value in the chain
     * @param $value
     * @return bool
     */
    abstract public function hasValue($value): bool;

    /**
     * Merges all elements with current chain
     * @param mixed ...$elements
     * @return Chain
     */
    abstract public function merge(...$elements): Chain;

    /**
     * Get keys of the chain
     * @return Chain
     */
    abstract public function keys(): Chain;

    /**
     * Appends value to the chain
     * @param $any
     * @return Chain
     */
    abstract public function append($any): Chain;

    /**
     * Sort chain by keys
     * @param callable|null $sortFn
     * @param int|null $direction
     * @param int|null $sortFlags
     * @return Chain
     */
    abstract public function sortKeys(
        ?callable $sortFn = null,
        ?int $direction = SORT_ASC,
        ?int $sortFlags = null
    ): Chain;

    /**
     * Get chain's chunk
     * @param int $startIncluded
     * @param int $stopExcluded
     * @param int $step
     * @param bool $saveIndex
     * @return Chain
     */
    abstract public function slice(
        int $startIncluded,
        int $stopExcluded,
        int $step = 1,
        bool $saveIndex = false
    ): Chain;

    /**
     * Sort chain by values. Index-safe operation
     * @param callable|null $sortFn
     * @param int|null $direction
     * @param int|null $sortFlags
     * @return Chain
     */
    abstract public function sortValues(
        ?callable $sortFn = null,
        ?int $direction = SORT_ASC,
        ?int $sortFlags = SORT_REGULAR
    ): Chain;

    /**
     * Filter the chain using function $fn
     * @param callable|null $fn
     * @param bool $saveIndex
     * @return Chain
     */
    abstract public function filter(?callable $fn = null, bool $saveIndex = false): Chain;

    /**
     * Just calls $fn for each chain element
     * @param callable $fn
     * @return Chain
     */
    abstract public function forEach(callable $fn): Chain;

    /**
     * Get chain values
     * @return Chain
     */
    abstract public function values(): Chain;

    /**
     * Calculates intersection of two sets (current chain and passed one). Non index-safe operation
     * @param Chain|iterable|mixed ...$elements
     * @return Chain
     */
    abstract public function intersect(...$elements): Chain;

    /**
     * Calculates intersection of two sets (current chain and passed one). Index-safe operation
     * @param Chain|iterable|mixed ...$elements
     * @return Chain
     */
    abstract public function intersectKeepIndexes(...$elements): Chain;

    /**
     * Sorts collection by given property
     *
     * @param string $propertyName
     *
     *  $propertyName eq to 'child.name' means $object->getChild()->getName() for each item in collection
     *
     *  $propertyName eq to '[child][name]' means $object['child']['name'] for each item in collection
     *
     * @param int $direction Available values are global PHP constants: SORT_ASC, SORT_DESC
     * @return Chain
     */
    abstract public function sortByProperty(string $propertyName, int $direction = SORT_ASC): Chain;

    /**
     * Works like {@see map} but returns iterable set of mapped objects per each the chain's item
     * @param callable $fn
     * @return Chain
     */
    abstract public function flatMap(callable $fn): Chain;

    /**
     * @return callable
     */
    protected function getKeysFunction(): callable
    {
        return static function (iterable $items) {
            $index = 0;
            foreach ($items as $k => $item) {
                yield $index++ => $k;
            }
        };
    }

    /**
     * @param bool $saveIndex
     * @return callable
     */
    protected function getReverseFunction(bool $saveIndex): callable
    {
        return function (iterable $items) use ($saveIndex) {
            yield from array_reverse($this->iterableToArray($items), $saveIndex);
        };
    }

    /**
     * Converts an iterable to an array
     * @param iterable $items
     * @return array
     */
    protected function iterableToArray(iterable $items): array
    {
        $array = [];
        foreach ($items as $k => $item) {
            $array[$k] = $item;
        }

        return $array;
    }

    /**
     * @param array $data
     * @return callable
     */
    protected function getMergeFunction(array $data): callable
    {
        return function (iterable $items) use ($data) {
            yield from array_merge($this->iterableToArray($items), $data);
        };
    }

    /**
     * @return callable
     */
    protected function getFlipFunction(): callable
    {
        return static function (iterable $items) {
            foreach ($items as $key => $item) {
                yield $item => $key;
            }
        };
    }

    /**
     * @param $any
     * @return callable
     */
    protected function getAppendFunction($any): callable
    {
        return static function (iterable $items) use ($any) {
            $lastIndex = null;
            foreach ($items as $index => $item) {
                if (is_int($index)) {
                    $lastIndex = $index;
                }
                yield $index => $item;
            }
            $lastIndex = $lastIndex === null ? 0 : ($lastIndex + 1);
            yield $lastIndex => $any;
        };
    }

    /**
     * @param bool|null $saveIndex
     * @return callable
     */
    protected function getUniqueFunction(?bool $saveIndex = false): callable
    {
        return static function (iterable $items) use ($saveIndex) {
            $cache = [];
            $counter = 0;
            foreach ($items as $index => $value) {
                if (!in_array($value, $cache, true)) {
                    yield $saveIndex ? $index : $counter++ => $cache[] = $value;
                }
            }
        };
    }

    /**
     * @param $any
     * @return callable
     */
    protected function getPrependFunction($any): callable
    {
        return static function (iterable $items) use ($any) {
            yield 0 => $any;
            foreach ($items as $key => $item) {
                yield is_int($key) ? $key + 1 : $key => $item;
            }
        };
    }

    /**
     * @param callable $fn
     * @return callable
     */
    protected function getMapFunction(callable $fn): callable
    {
        return static function (iterable $items) use ($fn) {
            foreach ($items as $k => $val) {
                yield $k => $fn($val, $k);
            }
        };
    }

    /**
     * @param array $data
     * @return callable
     */
    protected function getIntersectKeepIndexesFunction(array $data): callable
    {
        return static function (iterable $items) use ($data) {
            foreach ($items as $k => $value) {
                if (in_array($value, $data, true)) {
                    yield $k => $value;
                }
            }
        };
    }

    /**
     * @param callable|null $sortFn
     * @param int|null $direction
     * @param int|null $sortFlags
     * @return callable
     */
    protected function getSortKeysFunction(
        ?callable $sortFn,
        ?int $direction = SORT_ASC,
        ?int $sortFlags = SORT_REGULAR
    ): callable {
        return function (iterable $items) use ($sortFn, $direction, $sortFlags) {
            $direction = $direction ?? SORT_ASC;
            $items = $this->iterableToArray($items);
            $sortFn = $sortFn ?? null;
            if ($sortFn === null) {
                switch ($direction) {
                    case SORT_DESC:
                        krsort($items, $sortFlags ?? SORT_REGULAR);
                        break;
                    case SORT_ASC:
                    default:
                        ksort($items, $sortFlags ?? SORT_REGULAR);
                        break;
                }

            } else {
                uksort($items, $sortFn);
            }

            foreach ($items as $index => $item) {
                yield $index => $item;
            }
        };
    }

    /**
     * @param int $startIncluded
     * @param int $stopExcluded
     * @param int $step
     * @param bool $saveIndex
     * @return callable
     */
    protected function getSliceFunction(
        int $startIncluded,
        int $stopExcluded,
        int $step = 1,
        bool $saveIndex = false
    ): callable {
        return static function (iterable $items) use ($startIncluded, $stopExcluded, $step, $saveIndex) {
            $counter = 0;
            $pointer = 0;
            foreach ($items as $index => $value) {
                if ($pointer >= $startIncluded && $pointer < $stopExcluded && ($pointer - $startIncluded) % $step === 0) {
                    yield $saveIndex ? $index : $counter++ => $value;
                }
                $pointer++;
            }
        };
    }

    /**
     * @return callable
     */
    protected function getValuesFunction(): callable
    {
        return static function (iterable $items) {
            $index = 0;
            foreach ($items as $item) {
                yield $index++ => $item;
            }
        };
    }

    /**
     * @param callable|null $sortFn
     * @param int|null $direction
     * @param int|null $sortFlags
     * @return callable
     */
    protected function getSortValuesFunction(
        ?callable $sortFn,
        ?int $direction = SORT_ASC,
        ?int $sortFlags = SORT_REGULAR
    ): callable {
        return function (iterable $items) use ($sortFn, $direction, $sortFlags) {
            $direction = $direction ?? SORT_ASC;
            $items = $this->iterableToArray($items);
            $sortFn = $sortFn ?? null;
            if ($sortFn === null) {
                switch ($direction) {
                    case SORT_DESC:
                        arsort($items, $sortFlags ?? SORT_REGULAR);
                        break;
                    case SORT_ASC:
                    default:
                        asort($items, $sortFlags ?? SORT_REGULAR);
                        break;
                }

            } else {
                uasort($items, $sortFn);
            }

            foreach ($items as $index => $item) {
                yield $index => $item;
            }
        };
    }

    /**
     * @param callable|null $fn
     * @param bool $saveIndex
     * @return callable
     */
    protected function getFilterFunction(?callable $fn, bool $saveIndex = false): callable
    {
        return static function (iterable $items) use ($fn, $saveIndex) {
            $counter = 0;
            foreach ($items as $index => $value) {
                if ($fn === null) {
                    /** @noinspection TypeUnsafeComparisonInspection */
                    if ($value != false) {
                        yield $saveIndex ? $index : $counter++ => $value;
                    }
                } /** @noinspection TypeUnsafeComparisonInspection */ elseif ($fn($value) != false) {
                    yield $saveIndex ? $index : $counter++ => $value;
                }
            }
        };
    }

    /**
     * @param callable $fn
     * @return callable
     */
    protected function getForEachFunction(callable $fn): callable
    {
        return static function (iterable $items) use ($fn) {
            foreach ($items as $index => $item) {
                $fn($item, $index);
                yield $index => $item;
            }
        };
    }

    /**
     * @param array $data
     * @return callable
     */
    protected function getIntersectFunction(array $data): callable
    {
        return static function (iterable $items) use ($data) {
            $index = 0;
            foreach ($items as $value) {
                if (in_array($value, $data, true)) {
                    yield $index++ => $value;
                }
            }
        };
    }

    /**
     * @param array $data
     * @return callable
     */
    protected function getDiffFunction(array $data): callable
    {
        return function (iterable $items) use ($data) {
            $items = $this->iterableToArray($items);
            $comparator = static function ($a, $b) {
                if (is_object($a) && is_object($b)) {
                    return strcmp(spl_object_hash($a), spl_object_hash($b));
                }

                if ((is_object($a) && !is_object($b)) || (!is_object($a) && is_object($b))) {
                    throw new \InvalidArgumentException('Could not compare object with scalar');
                }

                return $a <=> $b;
            };
            yield from array_udiff($items, $data, $comparator);
        };
    }

    /**
     * @return callable
     */
    protected function getMixFunction(): callable
    {
        return function (iterable $items) {
            $items = $this->iterableToArray($items);
            shuffle($items);
            yield from $items;
        };
    }

    /**
     * @param $key
     * @param $value
     * @return callable
     */
    protected function getSetValueFunction($key, $value): callable
    {
        return static function (iterable $items) use ($key, $value) {
            $passed = false;
            foreach ($items as $index => $item) {
                if ($index === $key) {
                    $passed = true;
                    yield $index => $value;
                } else {
                    yield $index => $item;
                }
            }

            if (!$passed) {
                yield $key => $value;
            }
        };
    }

    /**
     * @param callable $fn
     * @return callable
     */
    protected function getFlatMapFunction(callable $fn): callable
    {
        return static function (iterable $items) use ($fn) {
            $index = 0;
            foreach ($items as $key => $value) {
                $result = $fn($value, $key);
                if (!is_iterable($result)) {
                    throw new \LogicException('Passed to flatMap() callback function must return iterable data');
                }

                foreach ($result as $item) {
                    yield $index++ => $item;
                }
            }
        };
    }

    /**
     * @param string $propertyName
     * @param int $direction
     * @return callable
     */
    protected function getSortByPropertyFunction(string $propertyName, int $direction): callable
    {
        if (!in_array($direction, [SORT_ASC, SORT_DESC], true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid sort direction "%s". Allowed value is one of the following builtin PHP constants: SORT_ASC or SORT_DESC',
                $direction
            ));
        }
        return function (iterable $items) use ($propertyName, $direction) {
            $items = $this->iterableToArray($items);
            $propAccessor = PropertyAccess::createPropertyAccessorBuilder()
                ->enableExceptionOnInvalidIndex()
                ->enableMagicCall()
                ->enableExceptionOnInvalidPropertyPath()
                ->getPropertyAccessor();
            uasort(
                $items,
                static function ($a, $b) use ($propertyName, $direction, $propAccessor) {
                    try {
                        return $direction === SORT_ASC
                            ? $propAccessor->getValue($a, $propertyName) <=> $propAccessor->getValue($b, $propertyName)
                            : $propAccessor->getValue($b, $propertyName) <=> $propAccessor->getValue($a, $propertyName);
                    } catch (NoSuchPropertyException $e) {
                        throw new \InvalidArgumentException(
                            sprintf('Bad property name: "%s". Got the error: "%s"', $propertyName, $e->getMessage()),
                            $e->getCode(),
                            $e
                        );
                    } catch (NoSuchIndexException $e) {
                        throw new \InvalidArgumentException(
                            sprintf('Bad index: "%s". Got the error: "%s"', $propertyName, $e->getMessage()),
                            $e->getCode(),
                            $e
                        );
                    }
                }
            );

            foreach ($items as $index => $item) {
                yield $index => $item;
            }
        };
    }

    /**
     * @return callable
     */
    protected function getFreqAnalysisFunction(): callable
    {
        return static function (iterable $items) {
            $histogram = [];
            foreach ($items as $item) {
                if (!is_scalar($item)) {
                    throw new \InvalidArgumentException(
                        'Only scalar values supported for building frequency analysis'
                    );
                }

                if (array_key_exists($item, $histogram)) {
                    $histogram[$item]++;
                } else {
                    $histogram[$item] = 1;
                }
            }
            yield from $histogram;
        };
    }

    protected function getRemoteFunction($key)
    {
        return static function (iterable $items) use ($key) {
            foreach ($items as $k => $v) {
                if ($k !== $key) {
                    yield $k => $v;
                }
            }
        };
    }
}