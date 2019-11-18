<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain;


use ArrayIterator;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Traversable;
use VKPHPUtils\Chain\Functor\IterableCallableOperator;
use VKPHPUtils\Chain\Functor\MapOperator;
use VKPHPUtils\Chain\Functor\Operator;

class Chain implements IteratorAggregate, \JsonSerializable
{

    /**
     * @var iterable
     */
    private $items;

    /**
     * @var Operator[]
     */
    private $operators;

    private function __construct(array $items)
    {

        $this->items = $items;
        $this->operators = [];
    }

    public function map(callable $mapFn): Chain
    {
        $this->operators[] = new MapOperator(
            Operator::TYPE_MAP, new class($mapFn)
            {
                /**
                 * @var callable
                 */
                private $mapFn;

                /**
                 *  constructor.
                 * @param callable $mapFn
                 */
                public function __construct(callable $mapFn)
                {
                    $this->mapFn = $mapFn;
                }

                public function __invoke($index, $value)
                {
                    yield $index => ($this->mapFn)($value);
                }
            }
        );

        return $this;
    }

    public function filter(?callable $filterFn = null, bool $saveIndexes = false): Chain
    {
        $this->operators[] = new MapOperator(
            Operator::TYPE_FILTER,
            new class($filterFn, $saveIndexes)
            {
                /**
                 * @var callable
                 */
                private $callback;

                private $saveIndex;

                private $index = 0;

                public function __construct(?callable $filterFn = null, bool $saveIndex = false)
                {
                    $this->callback = $filterFn;
                    $this->saveIndex = $saveIndex;
                }

                public function __invoke($index, $value, array $attributes = [])
                {
                    if (is_callable($this->callback)) {
                        /** @noinspection TypeUnsafeComparisonInspection */
                        if (($this->callback)($value) != false) {
                            if ($this->saveIndex) {
                                yield $index => $value;
                            } else {
                                yield $this->index++ => $value;
                            }
                        }
                    } /** @noinspection TypeUnsafeComparisonInspection */ elseif ($value != false) {
                        yield $this->saveIndex ? $index : $this->index++ => $value;
                    }
                }
            }
        );

        return $this;
    }

    public function forEach(callable $fn): Chain
    {
        $this->operators[] = new MapOperator(
            Operator::TYPE_FOREACH, new class($fn)
            {
                /**
                 * @var callable
                 */
                private $fn;

                public function __construct(callable $fn)
                {
                    $this->fn = $fn;
                }

                public function __invoke($index, $value)
                {
                    ($this->fn)($value);

                    yield $index => $value;
                }
            }
        );

        return $this;
    }

    public function flip(): Chain
    {
        $this->operators[] = new MapOperator(
            Operator::TYPE_FLIP,
            static function ($index, $value) {
                yield $value => $index;
            }
        );

        return $this;
    }

    public function keys(): Chain
    {
        $this->operators[] = new MapOperator(
            Operator::TYPE_KEYS, new class()
            {
                private $index = 0;

                public function __invoke($index, $value)
                {
                    yield $this->index++ => $index;
                }
            }
        );

        return $this;
    }

    public function values(): Chain
    {
        $this->operators[] = new MapOperator(
            Operator::TYPE_VALUES, new class
            {
                private $index = 0;

                public function __invoke($index, $value)
                {
                    yield ($this->index++) => $value;
                }
            }
        );

        return $this;
    }

    public function unique(bool $saveIndexes = false): Chain
    {
        $this->operators[] = new MapOperator(
            Operator::TYPE_UNIQUE, new class($saveIndexes)
        {
            private $index = 0;
            private $cache = [];
            /** @var bool */
            private $saveIndex;

            public function __construct(bool $saveIndexes)
            {
                $this->saveIndex = $saveIndexes;
            }

            public function __invoke($index, $value)
            {
                if (!in_array($value, $this->cache, true)) {
                    $this->cache[] = $value;
                    yield $this->saveIndex ? $index : $this->index++ => $value;
                }
            }
        }
        );

        return $this;
    }

    public function intersect(Chain $chain, bool $saveIndexes = false): Chain
    {
        $this->operators[] = new MapOperator(
            Operator::TYPE_INTERSECT,
            new class($chain, $saveIndexes)
            {
                private $index = 0;
                /**
                 * @var array
                 */
                private $chainCache;

                /**
                 * @var bool
                 */
                private $saveIndex;

                /**
                 *  constructor.
                 * @param Chain $chain
                 * @param bool $saveIndexes
                 */
                public function __construct(Chain $chain, bool $saveIndexes)
                {
                    $this->chainCache = $chain->toArray();
                    $this->saveIndex = $saveIndexes;
                }


                public function __invoke($index, $value, array $attributes)
                {
                    if (in_array($value, $this->chainCache, true)) {
                        yield $this->saveIndex ? $index : $this->index++ => $value;
                    }
                }
            }
        );

        return $this;
    }

    public function hasValue($value): bool
    {
        return in_array($value, $this->toArray(), true);
    }

    public function toArray(): array
    {
        if (count($this->operators) === 0) {
            return $this->items;
        }

        return $this->applyOperators($this->items);
    }

    private function applyOperators(array $items): array
    {
        $results[-1] = $items;
        foreach ($this->operators as $key => $function) {
            $results[$key] = [];
            if ($function instanceof MapOperator) {
                foreach ($results[$key - 1] as $k => $item) {
                    $gen = $function->getCallable()($k, $item, $function->getAttributes());
                    if ($gen instanceof Iterator) {
                        foreach ($gen as $index => $value) {
                            $results[$key][$index] = $value;
                        }
                    } elseif (is_array($gen)) {
                        [$index, $value] = $gen;
                        if ($index !== null && $value !== null) {
                            $results[$key][$index] = $value;
                        }
                    }
                }
            } elseif ($function instanceof IterableCallableOperator) {
                $result = $function->getCallable()($results[$key - 1], $function->getAttributes());
                foreach ($result as $index => $value) {
                    $results[$key][$index] = $value;
                }
            } else {
                throw new RuntimeException('Failed functor: '.$function->getType());
            }
            unset($results[$key - 1]);
        }

        $this->operators = [];

        return end($results);
    }

    public function flatMap(callable $fn): Chain
    {
        $this->operators[] = new MapOperator(Operator::TYPE_FLAT_MAP, new class($fn) {
            /**
             * @var callable
             */
            private $callable;

            private $index = 0;

            /**
             *  constructor.
             * @param callable $fn
             */
            public function __construct(callable $fn)
            {
                $this->callable = $fn;
            }

            public function __invoke($index, $value, array $attributes = [])
            {
                $result = ($this->callable)($value, $index);
                if (!is_iterable($result)) {
                    throw new \LogicException('Callable must return iterable result');
                }

                foreach ($result as $item) {
                    yield $this->index++ => $item;
                }
            }
        });
        return $this;
    }

    public function slice(int $firstIncluded, int $lastExcluded, int $step = 1, bool $saveIndexes = false): Chain
    {
        $this->operators[] = new MapOperator(
            Operator::TYPE_SLICE,
            new class($saveIndexes, $firstIncluded, $lastExcluded, $step)
            {
                private $index = 0;
                private $pointer = 0;
                /** @var bool */
                private $saveIndex;

                private $first;
                private $last;
                private $step;

                public function __construct(bool $saveIndexes, $first, $last, $step)
                {
                    $this->saveIndex = $saveIndexes;
                    $this->first = $first;
                    $this->last = $last;
                    $this->step = $step;
                }

                public function __invoke($index, $value)
                {
                    if ($this->pointer >= $this->first && $this->pointer < $this->last && ($this->pointer - $this->first) % $this->step === 0) {
                        yield $this->saveIndex ? $index : $this->index++ => $value;
                    }
                    $this->pointer++;
                }
            },
            ['first' => $firstIncluded, 'last' => $lastExcluded, 'step' => $step]
        );

        return $this;
    }

    public function hasKey($key): bool
    {
        return array_key_exists($key, $this->toArray());
    }

    public function reduce(callable $reduceFn, $initialValue = null)
    {
        return array_reduce($this->toArray(), $reduceFn, $initialValue);
    }

    public function isEmpty(): bool
    {
        return $this->size() === 0;
    }

    public function size(): int
    {
        return count($this->toArray());
    }

    public function reverse(bool $saveIndexes = false): Chain
    {
        $this->operators[] = new IterableCallableOperator(
            Operator::TYPE_REVERSE,
            static function (iterable $items, array $attributes) use($saveIndexes) {
                $items = array_reverse((array)$items, $saveIndexes);
                foreach ($items as $index => $item) {
                    yield $index => $item;
                }
            },
            ['saveIndex' => $saveIndexes]
        );

        return $this;
    }

    public function merge(Chain $chain): Chain
    {
        $this->operators[] = new IterableCallableOperator(
            Operator::TYPE_MERGE, new class($chain)
            {
                /**
                 * @var Chain
                 */
                private $chain;

                public function __construct(Chain $chain)
                {
                    $this->chain = $chain;
                }

                public function __invoke(iterable $items)
                {
                    $items = array_merge((array)$items, $this->chain->toArray());
                    foreach ($items as $index => $item) {
                        yield $index => $item;
                    }
                }
            }
        );

        return $this;
    }

    public function append($value): Chain
    {
        $this->operators[] = new IterableCallableOperator(
            Operator::TYPE_APPEND,
            static function (iterable $items) use($value) {
                $lastIndex = null;
                foreach ($items as $index => $item) {
                    if (is_int($index)) {
                        $lastIndex = $index;
                    }
                    yield $index => $item;
                }
                $lastIndex = $lastIndex === null ? 0 : ($lastIndex + 1);
                yield $lastIndex => $value;
            }
        );

        return $this;
    }

    public function prepend($value): Chain
    {
        $this->operators[] = new IterableCallableOperator(
            Operator::TYPE_PREPEND,
            static function (iterable $items) use($value) {
                yield 0 => $value;
                foreach ($items as $key => $item) {
                    if (is_int($key)) {
                        yield ($key + 1) => $item;
                    } else {
                        yield $key => $item;
                    }
                }
            }
        );

        return $this;
    }

    public function sortValues(?callable $sortFn = null, ?int $direction = null, ?int $sortFlags = null): Chain
    {
        $this->operators[] = new IterableCallableOperator(
            Operator::TYPE_SORT_VALUES,
            static function (iterable $items, array $attributes) {
                $direction = $attributes['direction'] ?? SORT_ASC;
                $items = (array)$items;
                $sortFn = $attributes['sorterFn'] ?? null;
                if ($sortFn === null) {
                    switch ($direction) {
                        case SORT_ASC:
                            asort($items, $attributes['flags'] ?? SORT_REGULAR);
                            break;
                        case SORT_DESC:
                            arsort($items, $attributes['flags'] ?? SORT_REGULAR);
                            break;
                        default:
                            throw new InvalidArgumentException(
                                sprintf(
                                    'Invalid direction: "%s". Valid values are the following constants: SORT_ASC, SORT_DESC.',
                                    $direction
                                )
                            );
                    }

                } else {
                    uasort($items, $sortFn);
                }

                foreach ($items as $index => $item) {
                    yield $index => $item;
                }
            }, ['sorterFn' => $sortFn, 'flags' => $sortFlags, 'direction' => $direction]
        );

        return $this;
    }

    /**
     * @param string $propertyName
     *
     *  $propertyName eq to 'child.name' means $object->getChild()->getName() for each item in collection
     *
     *  $propertyName eq to '[child][name]' means $object['child']['name'] for each item in collection
     *
     * @param int $direction
     * @return Chain
     */
    public function sortByProperty(string $propertyName, int $direction = SORT_ASC): Chain
    {
        $this->operators[] = new IterableCallableOperator(
            Operator::TYPE_SORT_VALUES,
            new class($propertyName, $direction)
            {

                /**
                 * @var string
                 */
                private $propertyName;

                /**
                 * @var int
                 */
                private $direction;

                /**
                 * @var PropertyAccessorInterface
                 */
                private $propAccessor;

                /**
                 *  constructor.
                 * @param string $propertyName
                 * @param int $direction
                 */
                public function __construct(string $propertyName, int $direction)
                {
                    if (!in_array($direction, [SORT_ASC, SORT_DESC], true)) {
                        throw new InvalidArgumentException(
                            sprintf(
                                'Invalid sort "%s". Available constants are: [%s]',
                                $direction,
                                implode(', ', ['SORT_ASC', 'SORT_DESC'])
                            )
                        );
                    }
                    $this->propertyName = $propertyName;
                    $this->direction = $direction;
                    $this->propAccessor = PropertyAccess::createPropertyAccessorBuilder()
                        ->enableExceptionOnInvalidIndex()
                        ->enableMagicCall()
                        ->enableExceptionOnInvalidPropertyPath()
                        ->getPropertyAccessor();
                }


                public function __invoke(iterable $items, array $attributes)
                {
                    $items = (array)$items;
                    uasort($items, $this->getSortFn());

                    foreach ($items as $index => $item) {
                        yield $index => $item;
                    }
                }

                private function getSortFn(): callable
                {
                    return function ($a, $b) {
                        return $this->direction === SORT_ASC
                            ? $this->propAccessor->getValue($a, $this->propertyName)
                            <=> $this->propAccessor->getValue($b, $this->propertyName)
                            : $this->propAccessor->getValue($b, $this->propertyName)
                            <=> $this->propAccessor->getValue($a, $this->propertyName);
                    };
                }
            }
        );

        return $this;
    }

    public function sortKeys(?callable $sortFn = null, ?int $direction = null, ?int $sortFlags = null): Chain
    {
        $this->operators[] = new IterableCallableOperator(
            Operator::TYPE_SORT_KEYS,
            static function (iterable $items, array $attributes) {
                $direction = $attributes['direction'] ?? SORT_ASC;
                $items = (array)$items;
                $sortFn = $attributes['sorterFn'] ?? null;
                if ($sortFn === null) {
                    switch ($direction) {
                        case SORT_ASC:
                            ksort($items, $attributes['flags'] ?? SORT_REGULAR);
                            break;
                        case SORT_DESC:
                            krsort($items, $attributes['flags'] ?? SORT_REGULAR);
                            break;
                        default:
                            throw new InvalidArgumentException(
                                sprintf(
                                    'Invalid direction: "%s". Valid values are the following constants: SORT_ASC, SORT_DESC.',
                                    $direction
                                )
                            );
                    }

                } else {
                    uksort($items, $sortFn);
                }

                foreach ($items as $index => $item) {
                    yield $index => $item;
                }
            }, ['sorterFn' => $sortFn, 'flags' => $sortFlags, 'direction' => $direction]
        );

        return $this;
    }

    public function toChain(): Chain
    {
        return self::of($this);
    }

    /**
     * @param array|Chain|Iterator|IteratorAggregate $items
     * @return Chain
     */
    public static function of($items = null): Chain
    {
        if ($items === null) {
            return new static([]);
        }
        if (is_array($items)) {
            return new static($items);
        }

        if ($items instanceof Iterator || $items instanceof IteratorAggregate) {
            $chain = [];
            foreach ($items as $index => $item) {
                $chain[$index] = $item;
            }
            return new static($chain);
        }

        if ($items instanceof self) {
            return new static($items->toArray());
        }

        throw new InvalidArgumentException('Invalid constructor argument');
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}