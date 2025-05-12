<?php

namespace Test\PhpDevCommunity\RequestKit;

use DateTime;
use PhpDevCommunity\RequestKit\Exceptions\InvalidDataException;
use PhpDevCommunity\RequestKit\Schema\Schema;
use PhpDevCommunity\RequestKit\Type;
use PhpDevCommunity\RequestKit\Utils\KeyValueObject;
use PhpDevCommunity\UniTester\TestCase;

class SchemaTest extends TestCase
{

    private ?Schema $schema = null;

    protected function setUp(): void
    {
        $this->schema = Schema::create([
            'name' => Type::string()->length(3, 100)->required(),
            'age' => Type::int()->min(18)->max(99),
            'email' => Type::email()->lowercase(),
            'date_of_birth' => Type::date()->format('Y-m-d'),
            'created_at' => Type::datetime()->format('Y-m-d H:i:s')->default(date('2025-01-01 12:00:00')),
            'active' => Type::bool()->strict(),
        ]);
    }

    protected function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    protected function execute(): void
    {
        $this->testValidData();
        $this->testInvalidEmail();
        $this->testEdgeCaseAge();
        $this->testStrictBool();
        $this->testMissingOptionalField();
        $this->testMissingRequiredField();
        $this->testMultipleValidationErrors();
        $this->testNestedData();
        $this->testCollection();
        $this->testArray();
        $this->testExtend();
        $this->testExampleData();

    }

    public function testValidData(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => 25,
            'email' => 'JOHN@EXAMPLE.COM',
            'date_of_birth' => '1990-01-01',
            'created_at' => '2023-01-01 12:00:00',
            'active' => true,
        ];

        $result = $this->schema->process($data);
        $result = $result->toArray();

        $this->assertStrictEquals($result['name'], 'John Doe');
        $this->assertStrictEquals($result['age'], 25);
        $this->assertStrictEquals($result['email'], 'john@example.com');
        $this->assertStrictEquals($result['date_of_birth']->format('Y-m-d'), '1990-01-01');
        $this->assertStrictEquals($result['created_at']->format('Y-m-d H:i:s'), '2023-01-01 12:00:00');
        $this->assertStrictEquals($result['active'], true);
    }

    public function testInvalidEmail(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => 25,
            'email' => 'invalid-email', // Email invalide
            'date_of_birth' => '1990-01-01',
            'created_at' => '2023-01-01 12:00:00',
            'active' => true,
        ];
        $this->expectException(InvalidDataException::class, function () use ($data) {
            try {
                $result = $this->schema->process($data);
            } catch (InvalidDataException $e) {
                $this->assertNotEmpty($e->getErrors());
                $this->assertEquals(1, count($e->getErrors()));
                $this->assertNotEmpty($e->getError('email'));
                throw $e;
            }
        });

    }

    public function testEdgeCaseAge(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => '18', // Âge limite
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
            'created_at' => '2023-01-01 12:00:00',
            'active' => true,
        ];

        $result = $this->schema->process($data);
        $result = $result->toArray();

        $this->assertEquals(18, $result['age']);
    }

    public function testStrictBool(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => 25,
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
            'created_at' => '2023-01-01 12:00:00',
            'active' => 1, //
        ];

        $this->expectException(InvalidDataException::class, function () use ($data) {
            try {
                $result = $this->schema->process($data);
            } catch (InvalidDataException $e) {
                $this->assertNotEmpty($e->getErrors());
                $this->assertEquals(1, count($e->getErrors()));
                $this->assertNotEmpty($e->getError('active'));
                throw $e;
            }
        });
    }

    public function testMissingOptionalField(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => 25,
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
            // 'created_at'
            'active' => true,
        ];

        $result = $this->schema->process($data);
        $result = $result->toArray();
        $this->assertInstanceOf(DateTime::class, $result['created_at']);
    }

    public function testMissingRequiredField(): void
    {

        $data = [
            // 'name' manquant
            'age' => 25,
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
            'created_at' => '2023-01-01 12:00:00',
            'active' => true,
        ];

        $this->expectException(InvalidDataException::class, function () use ($data) {
            try {
                $result = $this->schema->process($data);
            } catch (InvalidDataException $e) {
                $this->assertNotEmpty($e->getErrors());
                $this->assertEquals(1, count($e->getErrors()));
                $this->assertNotEmpty($e->getError('name'));
                throw $e;
            }
        });
    }

    public function testMultipleValidationErrors(): void
    {

        $data = [
            'name' => 'John Doe',
            'age' => 17, // Âge invalide
            'email' => 'invalid-email', // Email invalide
            'date_of_birth' => '1990-01-01',
            'created_at' => '2023-01-01 12:00:00',
            'active' => true,
        ];

        $this->expectException(InvalidDataException::class, function () use ($data) {
            try {
                $result = $this->schema->process($data);
            } catch (InvalidDataException $e) {
                $this->assertNotEmpty($e->getErrors());
                $this->assertEquals(2, count($e->getErrors()));
                $this->assertNotEmpty($e->getError('age'));
                $this->assertNotEmpty($e->getError('email'));
                throw $e;
            }
        });
    }

    public function testNestedData(): void
    {
        $schema = Schema::create([
            'user' => Type::item([
                'name' => Type::string()->length(20, 50)->required(),
                'age' => Type::int()->strict()->alias('my_age'),
                'roles' => Type::arrayOf(Type::string()->strict())->required(),
                'address' => Type::item([
                    'street' => Type::string()->length(15, 100),
                    'city' => Type::string()->allowed('Paris', 'London'),
                ]),
            ]),
        ]);

        $data = [
            'user' => [
//                'name' => 'John Doe',
                'my_age' => '25',
//                'roles' => [
//                    1,
//                    2,
//                ],
                'address' => [
                    'street' => 'Main Street',
                    'city' => 'New York',
                ]
            ],
        ];

        $this->expectException(InvalidDataException::class, function () use ($schema, $data) {
            try {
                $schema->process($data);
            } catch (InvalidDataException $e) {
                $this->assertNotEmpty($e->getErrors());
                $this->assertEquals(5, count($e->getErrors()));
                $this->assertNotEmpty($e->getError('user.name'));
                $this->assertNotEmpty($e->getError('user.age'));
                $this->assertNotEmpty($e->getError('user.address.street'));
                $this->assertNotEmpty($e->getError('user.address.city'));
                $this->assertNotEmpty($e->getError('user.roles'));
                throw $e;
            }
        });

    }

    public function testCollection(): void
    {
        $schema = Schema::create([
            'users' => Type::arrayOf(Type::item([
                'name' => Type::string()->length(3, 50)->required(),
                'age' => Type::int(),
                'roles' => Type::arrayOf(Type::string())->required(),
                'address' => Type::item([
                    'street' => Type::string()->length(5, 100),
                    'city' => Type::string()->allowed('Paris', 'London'),
                ]),
            ])),
        ]);

        $data = [
            'users' => [
                [
                    'name' => 'John Doe',
                    'age' => '25',
                    'roles' => [
                        1,
                        2,
                    ],
                    'address' => [
                        'street' => 'Main Street',
                        'city' => 'London',
                    ]
                ],
                [
                    'name' => 'Jane Doe',
                    'age' => '30',
                    'roles' => [
                        3,
                        4,
                    ],
                    'address' => [
                        'street' => 'Main Street',
                        'city' => 'Paris',
                    ]
                ],
            ]
        ];

        $result = $schema->process($data);
        $this->assertStrictEquals(2, count($result->get('users')));
        $this->assertStrictEquals('John Doe', $result->get('users.0.name'));
        $this->assertStrictEquals(25, $result->get('users.0.age'));
        $this->assertStrictEquals(2, count($result->get('users.0.roles')));
        $this->assertStrictEquals('Main Street', $result->get('users.0.address.street'));
        $this->assertStrictEquals('London', $result->get('users.0.address.city'));

        $this->assertStrictEquals('Jane Doe', $result->get('users.1.name'));
        $this->assertStrictEquals(30, $result->get('users.1.age'));
        $this->assertStrictEquals(2, count($result->get('users.1.roles')));
        $this->assertStrictEquals('Main Street', $result->get('users.1.address.street'));
        $this->assertStrictEquals('Paris', $result->get('users.1.address.city'));
    }

    private function testExtend()
    {

        $schema1 = Schema::create([
            'name' => Type::string()->length(20, 50)->required(),
            'age' => Type::int()->strict()->alias('my_age'),
            'roles' => Type::arrayOf(Type::string()->strict())->required(),
            'address' => Type::item([
                'street' => Type::string()->length(15, 100),
                'city' => Type::string()->allowed('Paris', 'London'),
            ]),
        ]);

        $schema2 = $schema1->extend([
            'password' => Type::string()->length(10, 100),
            'address' => Type::item([
                'zip' => Type::string()->length(5, 10),
            ]),
        ]);

        $this->assertStrictEquals(5, count($schema2->copyDefinitions()));
        /**
         * @var Type\ItemType $address
         */
        $address = $schema2->copyDefinitions()['address'];
        $this->assertStrictEquals(1, count($address->copyDefinitions()));

    }

    private function testExampleData()
    {

        $schema1 = Schema::create([
            'name' => Type::string()->length(20, 50)->required()->example('John Doe'),
            'age' => Type::int()->strict()->alias('my_age')->example(20),
            'roles' => Type::arrayOf(Type::string()->strict())->required()->example('admin'),
            'address' => Type::item([
                'street' => Type::string()->length(15, 100),
                'city' => Type::string()->allowed('Paris', 'London'),
            ])->example([
                    'street' => 'Main Street',
                    'city' => 'London',
                ]
            ),
        ]);

        $this->assertEquals($schema1->generateExampleData(), [
            'name' => 'John Doe',
            'age' => 20,
            'roles' => ['admin'],
            'address' => [
                'street' => 'Main Street',
                'city' => 'London',
            ]
        ]);

        $schema2 = Schema::create([
            'name' => Type::string()->length(20, 50)->required()->example('John Doe'),
            'age' => Type::int()->strict()->alias('my_age')->example(20),
            'roles' => Type::arrayOf(Type::string()->strict())->required()->example('admin'),
            'address' => Type::item([
                'street' => Type::string()->length(15, 100)->example('Main Street'),
                'city' => Type::string()->allowed('Paris', 'London')->example('London'),
            ]),
        ]);

        $this->assertEquals($schema2->generateExampleData(), [
            'name' => 'John Doe',
            'age' => 20,
            'roles' => ['admin'],
            'address' => [
                'street' => 'Main Street',
                'city' => 'London',
            ]
        ]);
    }

    private function testArray()
    {

        $schema = Schema::create([
            'roles' => Type::arrayOf(Type::string()->strict())->required()->example('admin'),
            'dependencies' => Type::arrayOf(Type::string()->strict())->acceptStringKeys()
        ]);

        $data = [
            'roles' => ['admin'],
            'dependencies' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ];
        $result = $schema->process($data);
        $this->assertStrictEquals('admin', $result->get('roles.0'));
        $this->assertStrictEquals('value1', $result->get('dependencies.key1'));
        $this->assertStrictEquals('value2', $result->get('dependencies.key2'));


        $schema = Schema::create([
            'roles' => Type::arrayOf(Type::string()->strict())->required()->example('admin')->acceptCommaSeparatedValues(),
        ]);

        $data = [
            'roles' => 'admin,user,manager',
        ];
        $result = $schema->process($data);
        $this->assertStrictEquals('admin', $result->get('roles.0'));
        $this->assertStrictEquals('user', $result->get('roles.1'));
        $this->assertStrictEquals('manager', $result->get('roles.2'));


        $schema = Schema::create([
            'autoload.psr-4' => Type::map(Type::string()->strict()->trim())->required(),
            'dependencies' => Type::map(Type::string()->strict()->trim())
        ]);

        $data = [
            'autoload.psr-4' => [
                'App\\' => 'app/',
            ],
            'dependencies' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ];
        $result = $schema->process($data);
        $this->assertInstanceOf( KeyValueObject::class, $result->get('autoload.psr-4'));
        $this->assertInstanceOf( KeyValueObject::class, $result->get('dependencies'));
        $this->assertEquals(1, count($result->get('autoload.psr-4')));
        $this->assertEquals(2, count($result->get('dependencies')));


        $schema = Schema::create([
            'autoload.psr-4' => Type::map(Type::string()->strict()->trim()),
            'dependencies' => Type::map(Type::string()->strict()->trim())
        ]);

        $data = [
            'autoload.psr-4' => [
            ],
        ];
        $result = $schema->process($data);
        $this->assertInstanceOf( KeyValueObject::class, $result->get('autoload.psr-4'));
        $this->assertInstanceOf( KeyValueObject::class, $result->get('dependencies'));
        $this->assertEquals(0, count($result->get('autoload.psr-4')));
        $this->assertEquals(0, count($result->get('dependencies')));

    }
}
