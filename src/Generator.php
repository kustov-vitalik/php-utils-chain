<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain;


class Generator implements \IteratorAggregate
{
    private \Iterator $iterator;
    private array $cache = [];

    public function __construct($iterator)
    {
        if (is_array($iterator)) {
            $this->iterator = new \ArrayIterator($iterator);
        } elseif ($iterator instanceof \IteratorAggregate) {
            try {
                $this->iterator = $iterator->getIterator();
            } catch (\Exception $e) {
                throw new \RuntimeException(
                    sprintf(
                        'Failed to get iterator on IteratorAggregate object of class "%s". Got the error: "%s"',
                        get_class($iterator),
                        $e->getMessage()
                    ), $e->getCode(), $e
                );
            }
        } elseif ($iterator instanceof \Iterator) {
            $this->iterator = $iterator;
        }

        assert($this->iterator instanceof \Iterator, 'Failed to initialize ' . self::class);
    }

    public function getIterator(): \Generator
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