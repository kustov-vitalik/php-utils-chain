<?php

declare(strict_types=1);


namespace VKPHPUtils\Tests\Chain\Model;


class Person
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    public $age;

    /**
     * @var Address
     */
    private $address;

    /**
     * @var string[]
     */
    private $children;

    /**
     * Person constructor.
     * @param string $name
     * @param int $age
     * @param Address $address
     * @param array $children
     */
    public function __construct(string $name, int $age, Address $address, array $children = [])
    {
        $this->name = $name;
        $this->age = $age;
        $this->address = $address;
        $this->children = $children;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @return string[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}