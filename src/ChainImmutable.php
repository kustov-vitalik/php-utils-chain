<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain;


class ChainImmutable extends Chain
{
    private function __construct(iterable $iterable)
    {
        $this->generator = new Generator($iterable);
    }

    /**
     * @inheritDoc
     */
    public function map(callable $fn): Chain
    {
        return new self($this->applyFn($this->getMapFunction($fn)));
    }

    /**
     * @param callable $fn
     * @return \Generator
     */
    private function applyFn(callable $fn): \Generator
    {
        yield from $fn($this->generator);
    }

    /**
     * @inheritDoc
     */
    public function reverse(bool $saveIndex = false): Chain
    {
        return new self($this->applyFn($this->getReverseFunction($saveIndex)));
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $items = [];
        foreach ($this->generator as $index => $item) {
            $items[$index] = $item;
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function unique(bool $saveIndex = false): Chain
    {
        return new self($this->applyFn($this->getUniqueFunction($saveIndex)));
    }

    /**
     * @inheritDoc
     */
    public function toChain(): Chain
    {
        return new self(
            $this->applyFn(
                static function (iterable $items) {
                    yield from $items;
                }
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function prepend($any): Chain
    {
        return new self($this->applyFn($this->getPrependFunction($any)));
    }

    /**
     * @inheritDoc
     */
    public function hasValue($value): bool
    {
        foreach ($this as $item) {
            if ($item === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function merge(...$elements): Chain
    {
        return new self($this->applyFn($this->getMergeFunction(self::of(...$elements)->toArray())));
    }

    /**
     * Initialize immutable chain
     * @param mixed ...$items
     * @return Chain
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
     * @return iterable
     */
    public function getIterator(): iterable
    {
        return $this->generator->getIterator();
    }

    /**
     * @inheritDoc
     */
    public function flip(): Chain
    {
        return new self($this->applyFn($this->getFlipFunction()));
    }

    /**
     * @inheritDoc
     */
    public function keys(): Chain
    {
        return new self($this->applyFn($this->getKeysFunction()));
    }

    /**
     * @inheritDoc
     */
    public function append($any): Chain
    {
        return new self($this->applyFn($this->getAppendFunction($any)));
    }

    /**
     * @inheritDoc
     */
    public function sortKeys(
        ?callable $sortFn = null,
        ?int $direction = SORT_ASC,
        ?int $sortFlags = SORT_REGULAR
    ): Chain {
        return new self($this->applyFn($this->getSortKeysFunction($sortFn, $direction, $sortFlags)));
    }

    /**
     * @inheritDoc
     */
    public function slice(int $startIncluded, int $stopExcluded, int $step = 1, bool $saveIndex = false): Chain
    {
        return new self($this->applyFn($this->getSliceFunction($startIncluded, $stopExcluded, $step, $saveIndex)));
    }

    /**
     * @inheritDoc
     */
    public function sortValues(
        ?callable $sortFn = null,
        ?int $direction = SORT_ASC,
        ?int $sortFlags = SORT_REGULAR
    ): Chain {
        return new self($this->applyFn($this->getSortValuesFunction($sortFn, $direction, $sortFlags)));
    }

    /**
     * @inheritDoc
     */
    public function filter(?callable $fn = null, bool $saveIndex = false): Chain
    {
        return new self($this->applyFn($this->getFilterFunction($fn, $saveIndex)));
    }

    /**
     * @inheritDoc
     */
    public function forEach(callable $fn): Chain
    {
        return new self($this->applyFn($this->getForEachFunction($fn)));
    }

    /**
     * @inheritDoc
     */
    public function values(): Chain
    {
        return new self($this->applyFn($this->getValuesFunction()));
    }

    /**
     * @inheritDoc
     */
    public function intersect(...$elements): Chain
    {
        return new self($this->applyFn($this->getIntersectFunction(self::of(...$elements)->toArray())));
    }

    /**
     * @inheritDoc
     */
    public function intersectKeepIndexes(...$elements): Chain
    {
        return new self($this->applyFn($this->getIntersectKeepIndexesFunction(self::of(...$elements)->toArray())));
    }

    /**
     * @inheritDoc
     */
    public function sortByProperty(string $propertyName, int $direction = SORT_ASC): Chain
    {
        return new self($this->applyFn($this->getSortByPropertyFunction($propertyName, $direction)));
    }

    /**
     * @inheritDoc
     */
    public function flatMap(callable $fn): Chain
    {
        return new self($this->applyFn($this->getFlatMapFunction($fn)));
    }

    /**
     * @inheritDoc
     */
    public function frequencyAnalysis(): Chain
    {
        return new self($this->applyFn($this->getFreqAnalysisFunction()));
    }

    /**
     * @inheritDoc
     */
    public function diff(...$elements): Chain
    {
        return new self($this->applyFn($this->getDiffFunction(self::of(...$elements)->toArray())));
    }

    /**
     * @inheritDoc
     */
    public function mix(): Chain
    {
        return new self($this->applyFn($this->getMixFunction()));
    }

    /**
     * @inheritDoc
     */
    public function setValue($key, $value): Chain
    {
        return new self($this->applyFn($this->getSetValueFunction($key, $value)));
    }

    /**
     * @inheritDoc
     */
    public function remove($key): Chain
    {
        return new self($this->applyFn($this->getRemoteFunction($key)));
    }
}