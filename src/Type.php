<?php

namespace PhpDevCommunity\RequestKit;
use PhpDevCommunity\RequestKit\Schema\Schema;
use PhpDevCommunity\RequestKit\Type\AbstractType;
use PhpDevCommunity\RequestKit\Type\ArrayOfType;
use PhpDevCommunity\RequestKit\Type\BoolType;
use PhpDevCommunity\RequestKit\Type\DateTimeType;
use PhpDevCommunity\RequestKit\Type\DateType;
use PhpDevCommunity\RequestKit\Type\EmailType;
use PhpDevCommunity\RequestKit\Type\FloatType;
use PhpDevCommunity\RequestKit\Type\IntType;
use PhpDevCommunity\RequestKit\Type\ItemType;
use PhpDevCommunity\RequestKit\Type\NumericType;
use PhpDevCommunity\RequestKit\Type\StringType;
use PhpDevCommunity\RequestKit\Utils\DateOnly;

final class Type
{

    public static function int(): IntType
    {
        return new IntType();
    }

    public static function string(): StringType
    {
        return new StringType();
    }

    public static function numeric(): NumericType
    {
        return new NumericType();
    }

    public static function bool(): BoolType
    {
        return new BoolType();
    }

    public static function date(): DateType
    {
        return new DateType();
    }

    public static function datetime(): DateTimeType
    {
        return new DateTimeType();
    }

    public static function float(): FloatType
    {
        return new FloatType();
    }

    public static function email(): EmailType
    {
        return new EmailType();
    }

    public static function item(array $definitions) : ItemType
    {
        return new ItemType(Schema::create($definitions));
    }
    public static function arrayOf(AbstractType $type) : ArrayOfType
    {
        return new ArrayOfType($type);
    }

    public static function typeObject(string $type): ?AbstractType
    {
        if ($type=== DateOnly::class) {
            return self::date();
        }

        if ($type === \DateTimeInterface::class || is_subclass_of( $type, \DateTimeInterface::class)) {
            return self::datetime();
        }

        return null;
    }

    public static function type(string $type): AbstractType
    {
        $definition = self::typeObject($type);
        if ($definition) {
            return $definition;
        }
        switch ($type) {
            case 'array_of_string':
                return self::arrayOf(self::type('string'));
            case 'array_of_int':
                return self::arrayOf(self::type('int'));
            case 'array_of_numeric':
                return self::arrayOf(self::type('numeric'));
            case 'array_of_date':
                return self::arrayOf(self::type('date'));
            case 'array_of_datetime':
                return self::arrayOf(self::type('datetime'));
            case 'array_of_float':
                return self::arrayOf(self::type('float'));
            case 'array_of_email':
                return self::arrayOf(self::type('email'));
            case 'int':
                return self::int();
            case 'string':
                return self::string();
            case 'numeric':
                return self::numeric();
            case 'bool':
                return self::bool();
            case 'date':
                return self::date();
            case 'datetime':
                return self::datetime();
            case 'float':
                return self::float();
            case 'email':
                return self::email();
            default:
                throw new \LogicException('Unknown type ' . $type);
        }

    }
}
