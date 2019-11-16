<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain\Functor;


class IterableCallableOperator extends Operator
{

    public const TYPES = [
        self::TYPE_MERGE,
        self::TYPE_APPEND,
        self::TYPE_PREPEND,
        self::TYPE_SORT_VALUES,
        self::TYPE_SORT_KEYS,
        self::TYPE_REVERSE,
    ];

    /**
     * @var string
     */
    private $type;

    public function __construct(string $type, callable $callable, ?array $attributes = [])
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported type "%s"', $type));
        }
        parent::__construct($callable, $attributes);
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}