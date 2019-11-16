<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain\Functor;


abstract class Operator
{
    // map types
    public const TYPE_MAP = 'map';
    public const TYPE_FILTER = 'filter';
    public const TYPE_FLIP = 'flip';
    public const TYPE_FOREACH = 'foreach';
    public const TYPE_KEYS = 'keys';
    public const TYPE_VALUES = 'values';
    public const TYPE_UNIQUE = 'unique';
    public const TYPE_SLICE = 'slice';
    public const TYPE_INTERSECT = 'intersect';

    // mapMany types
    public const TYPE_MERGE = 'merge';
    public const TYPE_APPEND = 'append';
    public const TYPE_PREPEND = 'prepend';
    public const TYPE_SORT_VALUES = 'sortValues';
    public const TYPE_SORT_KEYS = 'sortKeys';
    public const TYPE_REVERSE = 'reverse';

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var array
     */
    private $attributes;

    /**
     * AbstractFunctor constructor.
     * @param callable $callable
     * @param array $attributes
     */
    public function __construct(callable $callable, ?array $attributes = [])
    {
        $this->callable = $callable;
        $this->attributes = $attributes;
    }

    /**
     * @return callable
     */
    public function getCallable(): callable
    {
        return $this->callable;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    abstract public function getType(): string;
}