<?php

namespace Test\PhpDevCommunity\RequestKit;

use DateTime;
use PhpDevCommunity\RequestKit\Exceptions\InvalidDataException;
use PhpDevCommunity\RequestKit\Schema\Schema;
use PhpDevCommunity\RequestKit\Type;
use PhpDevCommunity\UniTester\TestCase;
use Test\PhpDevCommunity\RequestKit\Model\AddressTest;
use Test\PhpDevCommunity\RequestKit\Model\UserModelTest;

class HydratorTest extends TestCase
{

    private ?Schema $schema = null;

    protected function setUp(): void
    {

        $this->schema = Schema::createFromObject(UserModelTest::class); // Schema::create()
//        $this->schema->
//        $this->schema = Schema::create([
//            'name' => Type::string()->length(3, 100)->required(),
//            'age' => Type::int()->min(18)->max(99),
//            'email' => Type::email()->lowercase(),
//            'date_of_birth' => Type::date()->format('Y-m-d'),
//            'created_at' => Type::datetime()->format('Y-m-d H:i:s')->default(date('2025-01-01 12:00:00')),
//            'active' => Type::bool()->strict(),
//            'addresses' => Type::arrayOf(
//                Type::item([
//                    'street' => Type::string()->length(5, 100),
//                    'city' => Type::string()->allowed('Paris', 'London'),
//                ], AddressTest::class)
//            ),
//            'address' => Type::item([
//                'street' => Type::string()->length(5, 100),
//                'city' => Type::string()->allowed('Paris', 'London'),
//            ]),
//        ])->object(UserModelTest::class);
//
//        $schemav2 = $this->schema->extend([
//            ''
//        ])
    }

    protected function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    protected function execute(): void
    {
//        $this->testHydratorSimple();

    }

    public function testHydratorSimple(): void
    {
        $data = [
            'name' => 'John Doe',
            'active' => true,
            'address' => [
                'street' => '10 rue de la paix',
                'city' => 'Paris',
            ],
        ];

        $result = $this->schema->process($data);
        $result = $result->toObject();

        $this->assertInstanceOf(UserModelTest::class, $result);
        $this->assertStrictEquals('John Doe', $result->getName());
        $this->assertStrictEquals(null, $result->getAge());
        $this->assertStrictEquals(null, $result->getEmail());
        $this->assertInstanceOf(AddressTest::class, $result->getAddress());
        $this->assertStrictEquals('10 rue de la paix', $result->getAddress()->getStreet());
        $this->assertStrictEquals('Paris', $result->getAddress()->getCity());
        $this->assertInstanceOf(DateTime::class, $result->getCreatedAt());


        $data = [
            'name' => 'John Doe',
            'age' => 25,
            'email' => 'JOHN@EXAMPLE.COM',
            'date_of_birth' => '1990-01-01',
            'created_at' => '2023-01-01 12:00:00',
            'active' => true,
            'address' => null,
            'addresses' => null,
        ];

        $result = $this->schema->process($data);
        $result = $result->toObject();

        $this->assertInstanceOf(UserModelTest::class, $result);
        $this->assertStrictEquals('John Doe', $result->getName());
        $this->assertStrictEquals(25, $result->getAge());
        $this->assertStrictEquals('john@example.com', $result->getEmail());
        $this->assertInstanceOf(DateTime::class, $result->getDateOfBirth());
        $this->assertInstanceOf(DateTime::class, $result->getCreatedAt());
        $this->assertStrictEquals(null, $result->getAddress());
        $this->assertStrictEquals([], $result->getAddresses());

        $data = [
            'name' => 'John Doe',
            'age' => 25,
            'email' => 'JOHN@EXAMPLE.COM',
            'date_of_birth' => '1990-01-01',
            'created_at' => '2023-01-01 12:00:00',
            'active' => true,
            'address' => null,
            'addresses' => [
                [
                    'street' => '10 rue de la paix',
                    'city' => 'Paris',
                ],
                [
                    'street' => '11 rue de la paix',
                    'city' => 'Paris',
                ],
            ],
        ];

        $result = $this->schema->process($data);
        $result = $result->toObject();

        $this->assertInstanceOf(UserModelTest::class, $result);
        $this->assertStrictEquals('John Doe', $result->getName());
        $this->assertStrictEquals(25, $result->getAge());
        $this->assertStrictEquals('john@example.com', $result->getEmail());
        $this->assertInstanceOf(DateTime::class, $result->getDateOfBirth());
        $this->assertInstanceOf(DateTime::class, $result->getCreatedAt());
        $this->assertStrictEquals(null, $result->getAddress());
        $this->assertStrictEquals(2, count($result->getAddresses()));

    }
}
