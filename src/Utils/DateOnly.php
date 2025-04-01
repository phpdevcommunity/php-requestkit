<?php

namespace PhpDevCommunity\RequestKit\Utils;

final class DateOnly extends \DateTime
{
    public function __construct(string $date = "now", \DateTimeZone $timezone = null)
    {
        parent::__construct($date, $timezone);
        $this->setTime(0, 0, 0);
    }

    /**
     * @param $format
     * @param $datetime
     * @param $timezone
     * @return \DateTime|false
     * @throws \Exception
     */
    public static function createFromFormat($format, $datetime, $timezone = null)
    {
        $date = \DateTime::createFromFormat($format, $datetime, $timezone);
        if ($date === false) {
            return false;
        }
        return new self($date->format('Y-m-d'));
    }

    public function setTimestamp( $timestamp ): \DateTime
    {
        parent::setTimestamp($timestamp);
        $this->setTime(0, 0, 0);
        return $this;
    }
}
