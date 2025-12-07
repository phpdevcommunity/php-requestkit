<?php
namespace Test\Depo\RequestKit;
use Depo\RequestKit\Type;
use Depo\RequestKit\Type\BoolType;
use Depo\RequestKit\Type\DateTimeType;
use Depo\RequestKit\Type\DateType;
use Depo\RequestKit\Type\FloatType;
use Depo\RequestKit\Type\IntType;
use Depo\RequestKit\Type\NumericType;
use Depo\RequestKit\Type\StringType;

class TypeTest extends \Depo\UniTester\TestCase
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
        $this->testEqualsConstraint(); // Add new test method
    }

    private function testEqualsConstraint()
    {
        // 1. String equals: success
        $type = Type::string()->equals('admin');
        $result = $type->validate('admin');
        $this->assertTrue($result->isValid());
        $this->assertStrictEquals('admin', $result->getValue());

        // 2. String equals: failure
        $type = Type::string()->equals('admin');
        $result = $type->validate('user');
        $this->assertFalse($result->isValid());
        $this->assertEquals('The value does not match the expected value.', $result->getError());

        // 3. Integer equals: success
        $type = Type::int()->equals(123);
        $result = $type->validate(123);
        $this->assertTrue($result->isValid());
        $this->assertStrictEquals(123, $result->getValue());

        // 4. Integer equals: failure
        $type = Type::int()->equals(123);
        $result = $type->validate(456);
        $this->assertFalse($result->isValid());
        $this->assertEquals('The value does not match the expected value.', $result->getError());

        // 5. Optional field with equals: success on null
        $type = Type::string()->equals('secret_token')->optional();
        $result = $type->validate(null);
        $this->assertTrue($result->isValid());
        $this->assertNull($result->getValue());

        // 6. Required field with equals: failure on null
        $type = Type::string()->equals('secret_token')->required();
        $result = $type->validate(null);
        $this->assertFalse($result->isValid());
        $this->assertEquals('Value is required, but got null or empty string.', $result->getError());

        // 7. Equals after transformation
        $type = Type::string()->lowercase()->equals('admin');
        $result = $type->validate('ADMIN');
        $this->assertTrue($result->isValid());
        $this->assertStrictEquals('admin', $result->getValue());

        // 8. Security check: Ensure error message does not leak sensitive data
        $secret = 'super_secret_api_key_12345';
        $type = Type::string()->equals($secret);
        $result = $type->validate('wrong_key');
        $this->assertFalse($result->isValid());
        $this->assertEquals('The value does not match the expected value.', $result->getError());
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
        $this->assertEquals('Value must be at least 10 characters long.', $result->getError());


        $type->length(1, 3);
        $result = $type->validate("  test  ");
        $this->assertFalse($result->isValid());
        $this->assertEquals('Value cannot be longer than 3 characters.', $result->getError());

        $type->length(10, 20)->optional();
        $result = $type->validate(null);
        $this->assertTrue($result->isValid());
        $this->assertEquals(null, $result->getValue());

        $type->required();
        $result = $type->validate(null);
        $this->assertFalse($result->isValid());
        $this->assertEquals('Value is required, but got null or empty string.', $result->getError());


        $type->length(1);
        $result = $type->validate(123);
        $this->assertTrue($result->isValid());
        $this->assertEquals('123', $result->getValue());
        $this->assertEquals(123, $result->getRawValue());

        $type->strict();
        $result = $type->validate(123);
        $this->assertFalse($result->isValid());
        $this->assertEquals('Value must be a string, got: integer.', $result->getError());


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

        $intWithTransform = (new IntType())
            ->required()
            ->transform(function ($value) {
                {
                    if (!is_string($value)) {
                        return $value;
                    }

                    if (preg_match('/-?\d+(\.\d+)?/', $value, $match)) {
                        return $match[0];
                    }
                    return $value;
                }
            })
            ->min(1)
            ->max(12);

        $result = $intWithTransform->validate("5 UNION ALL");
        $this->assertTrue($result->isValid());
        $this->assertEquals(5,$result->getValue());


        $floatWithTransform = (new FloatType())
            ->required()
            ->transform(function ($value) {
                {
                    if (!is_string($value)) {
                        return $value;
                    }

                    if (preg_match('/-?\d+(\.\d+)?/', $value, $match)) {
                        return $match[0];
                    }
                    return $value;
                }
            })
            ->min(1)
            ->max(12);
        $result = $floatWithTransform->validate("3.04 OR 1=1");
        $this->assertTrue($result->isValid());
        $this->assertEquals(3.04,$result->getValue());

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
