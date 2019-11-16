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
     * Person constructor.
     * @param string $name
     * @param int $age
     * @param Address $address
     */
    public function __construct(string $name, int $age, Address $address)
    {
        $this->name = $name;
        $this->age = $age;
        $this->address = $address;
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
}