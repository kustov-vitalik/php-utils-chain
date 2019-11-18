<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain\Functor;


class MapOperator extends Operator
{
    public const TYPES = [
        self::TYPE_FILTER,
        self::TYPE_MAP,
        self::TYPE_FLAT_MAP,
        self::TYPE_FLIP,
        self::TYPE_FOREACH,
        self::TYPE_KEYS,
        self::TYPE_VALUES,
        self::TYPE_UNIQUE,
        self::TYPE_SLICE,
        self::TYPE_INTERSECT,
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * MapFunctor constructor.
     * @param string $type
     * @param callable $fn
     * @param array|null $attributes
     */
    public function __construct(string $type, callable $fn, ?array $attributes = [])
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported type "%s"', $type));
        }
        parent::__construct($fn, $attributes);
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}