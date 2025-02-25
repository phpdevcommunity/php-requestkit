<?php

namespace Test\PhpDevCommunity\RequestKit\Model;

class AddressTest
{
    /**
     * @var string
     */
    private string $street;
    private string $city;

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
}
