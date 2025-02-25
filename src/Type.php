<?php

namespace PhpDevCommunity\RequestKit;
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
        return new ItemType($definitions);
    }

    public static function arrayOf(AbstractType $type) : ArrayOfType
    {
        return new ArrayOfType($type);
    }
}
