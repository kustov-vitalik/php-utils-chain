<?php

declare(strict_types=1);


namespace VKPHPUtils\Chain\Tests\Model;


class Address
{

    /**
     * @var string
     */
    private $street;

    /**
     * @var array
     */
    public $home;

    /**
     * Address constructor.
     * @param string $street
     * @param int $homeNumber
     */
    public function __construct(string $street, int $homeNumber)
    {
        $this->street = $street;
        $this->home = ['number' => $homeNumber];
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }
}