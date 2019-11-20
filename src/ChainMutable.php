<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain;


use Traversable;

class ChainMutable extends Chain
{
    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var Generator[]
     */
    private $operatorsChain;

    private function __construct(iterable $items)
    {
        $this->generator = new Generator($items);
        $this->operatorsChain = [$this->generator];
    }

    /**
     * @inheritDoc
     */
    public function map(callable $fn): Chain
    {
        $this->operatorsChain[] = new Generator($this->applyFn(end($this->operatorsChain), $this->getMapFunction($fn)));

        return $this;
    }

    /**
     * @param iterable $items
     * @param callable $fn
     * @return \Generator
     */
    private function applyFn(iterable $items, callable $fn): \Generator
    {
        yield from $fn($items);
    }

    /**
     * @inheritDoc
     */
    public function reverse(bool $saveIndex = false): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getReverseFunction($saveIndex)
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasKey($key): bool
    {
        foreach ($this as $k => $v) {
            if ($k === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        foreach ($this as $item) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function size(): int
    {
        return count($this->toArray());
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $items = [];
        foreach ($this as $k => $value) {
            $items[$k] = $value;
        }
        $this->operatorsChain = [$this->generator];

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function values(): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getValuesFunction()
            )
        );

        return $this;
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
        $generator = end($this->operatorsChain);
        $this->operatorsChain = [$this->generator];

        return $generator->getIterator();
    }

    /**
     * @inheritDoc
     */
    public function forEach(callable $fn): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getForEachFunction($fn)
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function filter(?callable $fn = null, bool $saveIndex = false): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getFilterFunction($fn, $saveIndex)
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function slice(int $startIncluded, int $stopExcluded, int $step = 1, bool $saveIndex = false): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getSliceFunction($startIncluded, $stopExcluded, $step, $saveIndex)
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function append($any): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getAppendFunction($any)
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function keys(): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getKeysFunction()
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flip(): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getFlipFunction()
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function merge(...$elements): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getMergeFunction(Chain::immutable(...$elements)->toArray())
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasValue($value): bool
    {
        foreach ($this as $k => $v) {
            if ($v === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function prepend($any): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getPrependFunction($any)
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toChain(): Chain
    {
        return self::of($this->toArray());
    }

    /**
     * @inheritDoc
     */
    public static function of(...$items): Chain
    {
        $firstItem = reset($items);
        if (is_iterable($firstItem) && count($items) === 1) {
            if ($firstItem instanceof Chain) {
                return new static($firstItem->getIterator());
            }

            return new static($firstItem);
        }

        if (count($items) === 0) {
            return new static([]);
        }

        return new static($items);
    }

    /**
     * @inheritDoc
     */
    public function unique(bool $saveIndex = false): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getUniqueFunction($saveIndex)
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function intersect(...$elements): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getIntersectFunction(self::immutable(...$elements)->toArray())
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function intersectKeepIndexes(...$elements): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getIntersectKeepIndexesFunction(self::immutable(...$elements)->toArray())
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sortValues(?callable $sortFn = null, ?int $direction = null, ?int $sortFlags = null): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getSortValuesFunction($sortFn, $direction, $sortFlags)
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sortKeys(?callable $sortFn = null, ?int $direction = null, ?int $sortFlags = null): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getSortKeysFunction($sortFn, $direction, $sortFlags)
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sortByProperty(string $propertyName, int $direction = SORT_ASC): Chain
    {

        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getSortByPropertyFunction($propertyName, $direction)
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flatMap(callable $fn): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(
                end($this->operatorsChain),
                $this->getFlatMapFunction($fn)
            )
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function reduce(callable $reduceFn, $initialValue = null)
    {
        return array_reduce($this->toArray(), $reduceFn, $initialValue);
    }

    /**
     * @inheritDoc
     */
    public function frequencyAnalysis(): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(end($this->operatorsChain), $this->getFreqAnalysisFunction())
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function diff(...$elements): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(end($this->operatorsChain), $this->getDiffFunction(self::of(...$elements)->toArray()))
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mix(): Chain
    {
        $this->operatorsChain[] = new Generator($this->applyFn(end($this->operatorsChain), $this->getMixFunction()));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setValue($key, $value): Chain
    {
        $this->operatorsChain[] = new Generator(
            $this->applyFn(end($this->operatorsChain), $this->getSetValueFunction($key, $value))
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getValue($key)
    {
        foreach ($this as $k => $v) {
            if ($k === $key) {
                return $v;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function search($value)
    {
        foreach ($this as $k => $v) {
            if ($v === $value) {
                return $k;
            }
        }

        return null;
    }


}