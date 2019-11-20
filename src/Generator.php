<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain;


use Traversable;

class Generator implements \IteratorAggregate
{
    /**
     * @var iterable
     */
    private $iterator;

    private $cache = [];

    /**
     * Generator constructor.
     * @param iterable $iterator
     */
    public function __construct(iterable $iterator)
    {
        if (is_array($iterator)) {
            $this->iterator = new \ArrayIterator($iterator);
        } elseif ($iterator instanceof \IteratorAggregate) {
            $this->iterator = $iterator->getIterator();
        } elseif ($iterator instanceof \Iterator) {
            $this->iterator = $iterator;
        } else {
            throw new \RuntimeException('Only array, Iterator, IteratorAggregate supported');
        }
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
        foreach ($this->cache as $index => $item) {
            yield $index => $item;
        }

        while ($this->iterator->valid()) {
            $this->cache[$key = $this->iterator->key()] = $this->iterator->current();
            $this->iterator->next();
            yield $key => $this->cache[$key];
        }
    }
}