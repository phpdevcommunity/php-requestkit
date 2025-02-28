<?php
namespace Test\PhpDevCommunity\RequestKit;
use PhpDevCommunity\RequestKit\Type\BoolType;
use PhpDevCommunity\RequestKit\Type\DateTimeType;
use PhpDevCommunity\RequestKit\Type\DateType;
use PhpDevCommunity\RequestKit\Type\IntType;
use PhpDevCommunity\RequestKit\Type\NumericType;
use PhpDevCommunity\RequestKit\Type\StringType;

class TypeTest extends \PhpDevCommunity\UniTester\TestCase
{

    protected function setUp(): void
    {
        // TODO: Implement setUp() method.
    }

    protected function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    protected function execute(): void
    {
        $this->testStringType();
        $this->testIntType();
        $this->testBoolType();
        $this->testDateTimeType();
        $this->testDateType();
        $this->testNumericType();
    }

    private function testStringType()
    {
        $type = (new StringType())
            ->required()
            ->length(4, 20);
        $result = $type->validate("  test  ");
        $this->assertTrue($result->isValid());
        $this->assertEquals('  test  ', $result->getValue());


        $type->trim();
        $result = $type->validate("  test  ");
        $this->assertTrue($result->isValid());
        $this->assertEquals('test', $result->getValue());


        $type->length(10, 20);
        $result = $type->validate("  test  ");
        $this->assertFalse($result->isValid());
        $this->assertEquals('test must be at least 10 characters long', $result->getError());


        $type->length(1, 3);
        $result = $type->validate("  test  ");
        $this->assertFalse($result->isValid());
        $this->assertEquals('test cannot be longer than 3 characters', $result->getError());

        $type->length(10, 20)->optional();
        $result = $type->validate(null);
        $this->assertTrue($result->isValid());
        $this->assertEquals(null, $result->getValue());

        $type->required();
        $result = $type->validate(null);
        $this->assertFalse($result->isValid());
        $this->assertEquals('Value is required, but got null or empty string', $result->getError());


        $type->length(1);
        $result = $type->validate(123);
        $this->assertTrue($result->isValid());
        $this->assertEquals('123', $result->getValue());
        $this->assertEquals(123, $result->getRawValue());

        $type->strict();
        $result = $type->validate(123);
        $this->assertFalse($result->isValid());
        $this->assertEquals('Value must be a string, got: integer', $result->getError());


        $type->uppercase();
        $result = $type->validate("is test");
        $this->assertTrue($result->isValid());
        $this->assertStrictEquals('IS TEST', $result->getValue());

        $type->lowercase();
        $type->removeSpaces();
        $result = $type->validate("is test for me");
        $this->assertTrue($result->isValid());
        $this->assertStrictEquals('istestforme', $result->getValue());

        $type->length(6);
        $type->padLeft(6, "0");
        $result = $type->validate("1");
        $this->assertTrue($result->isValid());
        $this->assertStrictEquals('000001', $result->getValue());

        $type->length(6);
        $type->removeChars('+', '-', '.');
        $result = $type->validate("123-45+6.");
        $this->assertTrue($result->isValid());
        $this->assertStrictEquals('123456', $result->getValue());

        $type->allowed('123456', '654321');
        $result = $type->validate("654321");
        $this->assertTrue($result->isValid());
        $this->assertStrictEquals('654321', $result->getValue());

        $type->allowed('123456', '654321');
        $result = $type->validate("254321");
        $this->assertFalse($result->isValid());
        $this->assertNotNull($result->getError());

    }

    private function testIntType()
    {
        $type = (new IntType())
            ->required()
            ->min(5)
            ->max(12);
        $result = $type->validate(12);
        $this->assertTrue($result->isValid());
        $this->assertEquals(12, $result->getValue());

        $result = $type->validate('12');
        $this->assertTrue($result->isValid());
        $this->assertEquals(12, $result->getValue());

        $result = $type->validate(1);
        $this->assertFalse($result->isValid());
        $this->assertNotNull($result->getError());

        $type->strict();
        $result = $type->validate("10");
        $this->assertFalse($result->isValid());
        $this->assertNotNull($result->getError());

        $result = $type->validate(null);
        $this->assertFalse($result->isValid());
        $this->assertNotNull($result->getError());

        $type->optional();
        $result = $type->validate(null);
        $this->assertTrue($result->isValid());
        $this->assertNull($result->getError());

    }

    private function testBoolType()
    {
        $type = (new BoolType())
            ->required();
        $result = $type->validate(true);
        $this->assertTrue($result->isValid());
        $this->assertEquals(true, $result->getValue());

        $result = $type->validate('true');
        $this->assertTrue($result->isValid());
        $this->assertEquals(true, $result->getValue());

        $result = $type->validate('1');
        $this->assertTrue($result->isValid());
        $this->assertEquals(true, $result->getValue());

        $result = $type->validate(1);
        $this->assertTrue($result->isValid());
        $this->assertEquals(true, $result->getValue());


        $result = $type->validate(false);
        $this->assertTrue($result->isValid());
        $this->assertEquals(false, $result->getValue());

        $result = $type->validate('false');
        $this->assertTrue($result->isValid());
        $this->assertEquals(false, $result->getValue());

        $result = $type->validate('0');
        $this->assertTrue($result->isValid());
        $this->assertEquals(false, $result->getValue());

        $result = $type->validate(0);
        $this->assertTrue($result->isValid());
        $this->assertEquals(false, $result->getValue());


    }

    private function testDateTimeType()
    {
        $type = (new DateTimeType())
            ->format('Y-m-d H:i:s')
            ->required();
        $result = $type->validate('2020-01-01 10:00:00');
        $this->assertTrue($result->isValid());
        $this->assertInstanceOf(\DateTime::class, $result->getValue());

        $type->optional();
        $result = $type->validate(null);
        $this->assertTrue($result->isValid());
        $this->assertEquals(null, $result->getValue());

        $type->required();
        $result = $type->validate('2020-01-01 10:00');
        $this->assertFalse($result->isValid());
        $this->assertNotNull($result->getError());
        ;
        $result = $type->validate(strtotime('2020-01-01 10:00'));
        $this->assertTrue($result->isValid());
        $this->assertInstanceOf(\DateTime::class, $result->getValue());
        $datetime = $result->getValue();
        $this->assertEquals('2020-01-01 10:00:00', $datetime->format('Y-m-d H:i:s'));

    }

    private function testDateType()
    {
        $type = (new DateType())
            ->format('Y-m-d')
            ->required();
        $result = $type->validate('2020-01-01');
        $this->assertTrue($result->isValid());
        $this->assertInstanceOf(\DateTime::class, $result->getValue());

        $type->optional();
        $result = $type->validate(null);
        $this->assertTrue($result->isValid());
        $this->assertEquals(null, $result->getValue());

        $type->required();
        $result = $type->validate('2020-01-01 10:00');
        $this->assertFalse($result->isValid());
        $this->assertNotNull($result->getError());

        $result = $type->validate(strtotime('2020-01-01'));
        $this->assertTrue($result->isValid());
        $this->assertInstanceOf(\DateTime::class, $result->getValue());
        $datetime = $result->getValue();
        $this->assertEquals('2020-01-01', $datetime->format('Y-m-d'));

    }

    private function testNumericType()
    {
        $testCases = [
            [1, '1'],
            ['1', '1'],
            ['1.0', '1.0'],
            [1.0, '1'],
            [0, '0'],
            [0.0, '0'],
            ['0.0', '0.0'],
            ['136585.589', '136585.589'],
            [136585.589, '136585.589'],
            [-1, "-1"],
            [-1.5,'-1.5'],
            [PHP_INT_MAX, (string)PHP_INT_MAX],
        ];

        foreach ($testCases as [$input, $expectedOutput]) {
            $type = (new NumericType())->required();
            $result = $type->validate($input);
            $this->assertTrue($result->isValid());
            $this->assertStrictEquals($expectedOutput, $result->getValue());
        }


    }
}
