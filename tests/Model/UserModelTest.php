<?php

namespace Test\PhpDevCommunity\RequestKit\Model;

use PhpDevCommunity\RequestKit\Utils\DateOnly;

class UserModelTest
{
    private string $name = '';
    private ?int $age = null; // Nullable as it's not required
    private ?string $email = null; // Nullable as it's not required
    private ?DateOnly $dateOfBirth = null; // Nullable as it's not required
    private ?\DateTimeInterface $createdAt = null;
    private ?AddressTest $address = null;

    /**
     * @var array<AddressTest>
     */
    private array $addresses = [];
    private bool $active = false;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): UserModelTest
    {
        $this->name = $name;
        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): UserModelTest
    {
        $this->age = $age;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): UserModelTest
    {
        $this->email = $email;
        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): UserModelTest
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): UserModelTest
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getAddress(): ?AddressTest
    {
        return $this->address;
    }

    public function setAddress(?AddressTest $address): UserModelTest
    {
        $this->address = $address;
        return $this;
    }

    public function getAddresses(): array
    {
        return $this->addresses;
    }

    public function setAddresses(array $addresses): UserModelTest
    {
        $this->addresses = $addresses;
        return $this;
    }
    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): UserModelTest
    {
        $this->active = $active;
        return $this;
    }
}
