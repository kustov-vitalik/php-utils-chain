<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain;


use ArrayIterator;
use Generator;
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
                    return [$index, ($this->mapFn)($value)];
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
                        if (($this->callback)($value) == false) {
                            return [null, null];
                        }

                        if ($this->saveIndex) {
                            return [$index, $value];
                        }

                        return [$this->index++, $value];
                    }

                    if ($value == false) {
                        return [null, null];
                    }

                    if ($this->saveIndex) {
                        return [$index, $value];
                    }

                    return [$this->index++, $value];

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

                    return [$index, $value];
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
                return [$value, $index];
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
                    return [$this->index++, $index];
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
                    return [$this->index++, $value];
                }
            }
        );

        return $this;
    }

    public function unique(bool $saveIndexes = false): Chain
    {
        $this->operators[] = new MapOperator(
            Operator::TYPE_UNIQUE, new class
        {
            private $index = 0;
            private $cache = [];

            public function __invoke($index, $value, array $attributes = [])
            {
                if (!in_array($value, $this->cache, true)) {
                    $this->cache[] = $value;
                    if ($attributes['saveIndexes']) {
                        return [$index, $value];
                    }

                    return [$this->index++, $value];
                }

                return [null, null];
            }
        }, ['saveIndexes' => $saveIndexes]
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
                        if ($this->saveIndex) {
                            return [$index, $value];
                        }

                        return [$this->index++, $value];
                    }

                    return [null, null];
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
                    [$index, $value] = $function->getCallable()($k, $item, $function->getAttributes());
                    if ($index !== null && $value !== null) {
                        $results[$key][$index] = $value;
                    }
                }
            } elseif ($function instanceof IterableCallableOperator) {
                $results[$key] = $function->getCallable()($results[$key - 1], $function->getAttributes());
            } else {
                throw new RuntimeException('Failed functor: '.$function->getType());
            }
            unset($results[$key - 1]);
        }

        $this->operators = [];

        return end($results);
    }

    public function slice(int $firstIncluded, int $lastExcluded, int $step = 1, bool $saveIndexes = false): Chain
    {
        $this->operators[] = new MapOperator(
            Operator::TYPE_SLICE,
            new class
            {
                private $index = 0;
                private $resultIndex = 0;

                public function __invoke($index, $value, array $attributes = [])
                {
                    $first = $attributes['first'];
                    $last = $attributes['last'];
                    $step = $attributes['step'];
                    $saveIndexes = $attributes['saveIndexes'];

                    $return = [null, null];
                    if ($this->index >= $first && $this->index < $last && ($this->index - $first) % $step === 0) {
                        if ($saveIndexes) {
                            $return = [$index, $value];
                        } else {
                            $return = [$this->resultIndex++, $value];
                        }
                    }

                    $this->index++;

                    return $return;
                }
            },
            ['first' => $firstIncluded, 'last' => $lastExcluded, 'step' => $step, 'saveIndexes' => $saveIndexes]
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
            static function (iterable $items, array $attributes) {
                return array_reverse((array)$items, $attributes['saveIndex'] ?? false);
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
                    return array_merge((array)$items, $this->chain->toArray());
                }
            }
        );

        return $this;
    }

    public function append($value): Chain
    {
        $this->operators[] = new IterableCallableOperator(
            Operator::TYPE_APPEND,
            static function (iterable $items, array $attributes) {
                $value = $attributes['value'];
                $items = (array)$items;
                $items[] = $value;

                return $items;
            }, ['value' => $value]
        );

        return $this;
    }

    public function prepend($value): Chain
    {
        $this->operators[] = new IterableCallableOperator(
            Operator::TYPE_PREPEND,
            static function (iterable $items, array $attributes) {
                $value = $attributes['value'];
                $items = (array)$items;
                array_unshift($items, $value);
                return $items;
            }, ['value' => $value]
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

                return $items;
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

                    return $items;
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

                return $items;
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