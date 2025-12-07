<?php

namespace Test\Depo\RequestKit\Model;

class AddressTest
{
    /**
     * @var string
     * @example toto
     */
    public string $street = '';
    public string $city = '';

    /**
     * @var array<string>
     */
    private array $tags = [];

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): AddressTest
    {
        $this->street = $street;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): AddressTest
    {
        $this->city = $city;
        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): AddressTest
    {
        $this->tags = $tags;
        return $this;
    }
}
