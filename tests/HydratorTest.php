<?php

namespace Test\PhpDevCommunity\RequestKit;

use DateTime;
use PhpDevCommunity\RequestKit\Builder\RequestKitBuilderFactory;
use PhpDevCommunity\RequestKit\Builder\SchemaObjectFactory;
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
        $user = new UserModelTest();
        $user->setName('John Doe 3');
        $user->setActive(false);
        $this->schema = (new SchemaObjectFactory(sys_get_temp_dir()))->createSchemaFromObject($user);
        $this->schema  = $this->schema->extend([
            'email' => Type::email()->lowercase(),
        ]);
    }

    protected function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    protected function execute(): void
    {
        $this->testHydratorSimple();
        $this->testAddingNewProperty();
        $this->testForceNullOnDefaultValue();
        $this->testMissingNestedProperty();
        $this->testEmptyArrayForAddresses();
        $this->testInvalidTypeForName();
        $this->testInvalidDate();

    }

    public function testHydratorSimple(): void
    {
        $data = [
            'name' => 'John Doe 3',
            'active' => true,
//            'created_at' => null,
            'address' => [
                'street' => '10 rue de la paix',
                'city' => 'Paris',
            ],
        ];
        $result = $this->schema->process($data);
        $result = $result->toObject();

        $this->assertInstanceOf(UserModelTest::class, $result);
        $this->assertStrictEquals('John Doe 3', $result->getName());
        $this->assertStrictEquals(null, $result->getAge());
        $this->assertStrictEquals(null, $result->getEmail());
        $this->assertInstanceOf(AddressTest::class, $result->getAddress());
        $this->assertStrictEquals('10 rue de la paix', $result->getAddress()->getStreet());
        $this->assertStrictEquals('Paris', $result->getAddress()->getCity());
        $this->assertInstanceOf(DateTime::class, $result->getCreatedAt());
        $this->assertStrictEquals(true, $result->isActive());


        $data = [
            'name' => 'John Doe',
            'age' => 25,
            'email' => 'JOHN@EXAMPLE.COM',
            'date_of_birth' => '1990-01-01',
            'created_at' => '2023-01-01 12:00:00',
            'active' => 'off',
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
        $this->assertStrictEquals(false, $result->isActive());
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
                    'tags' => [
                        'tag1',
                        'tag2',
                        1
                    ],
                ],
                [
                    'street' => 'Marsupilami',
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

        $this->assertStrictEquals('10 rue de la paix', $result->getAddresses()[0]->getStreet());
        $this->assertStrictEquals('Paris', $result->getAddresses()[0]->getCity());
        $this->assertStrictEquals(['tag1', 'tag2', '1'], $result->getAddresses()[0]->getTags());

        $this->assertStrictEquals('Marsupilami', $result->getAddresses()[1]->getStreet());
        $this->assertStrictEquals('Paris', $result->getAddresses()[1]->getCity());
        $this->assertStrictEquals([], $result->getAddresses()[1]->getTags());

    }

    public function testAddingNewProperty(): void
    {
        $data = [
            'phone' => '0606060606',
        ];

        $result = $this->schema->process($data);
        $result = $result->toArray();
        $this->assertTrue(!isset($result['phone']));

    }

    public function testForceNullOnDefaultValue(): void
    {
        $data = [
            'email' => null,
        ];

        $result = $this->schema->process($data);
        $result = $result->toObject();

        $this->assertNull($result->getEmail()); // Attendu : null ou "John Doe 3" si l'hydrateur ignore les nulls
    }

    public function testMissingNestedProperty(): void
    {
        $data = [
            'address' => [
                'street' => '10 rue de la paix',
                // 'city' est manquant
            ],
        ];

        $this->expectException(InvalidDataException::class, function () use ($data) {
            try {
                $this->schema->process($data);
            } catch (InvalidDataException $e) {
                $this->assertNotEmpty($e->getErrors());
                $this->assertEquals(1, count($e->getErrors()));
                $this->assertNotEmpty($e->getError('address.city'));
                throw $e;
            }
        });
    }

    public function testEmptyArrayForAddresses(): void
    {
        $data = [
            'addresses' => [], // Devrait être une liste d'adresses, mais on met un tableau vide
        ];

        $result = $this->schema->process($data);
        $result = $result->toObject();

        $this->assertTrue(is_array($result->getAddresses()));
        $this->assertStrictEquals(0, count($result->getAddresses())); // Doit retourner un tableau vide
    }

    public function testInvalidTypeForName(): void
    {
        $data = [
            'name' => ['John', 'Doe'], // Mauvais type (tableau au lieu de string)
        ];

        $this->expectException(InvalidDataException::class, function () use ($data) {
            try {
                $this->schema->process($data);
            } catch (InvalidDataException $e) {
                $this->assertNotEmpty($e->getErrors());
                $this->assertEquals(1, count($e->getErrors()));
                $this->assertNotEmpty($e->getError('name'));
                throw $e;
            }
        });
    }

    public function testInvalidDate(): void
    {
        $data = [
            'date_of_birth' => '99-99-9999', // Date invalide
        ];
        $this->expectException(InvalidDataException::class, function () use ($data) {
            try {
                $this->schema->process($data);
            } catch (InvalidDataException $e) {
                $this->assertNotEmpty($e->getErrors());
                $this->assertEquals(1, count($e->getErrors()));
                $this->assertNotEmpty($e->getError('date_of_birth'));
                throw $e;
            }
        });
    }



}
