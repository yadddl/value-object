<?php

declare(strict_types=1);

namespace Yadddl\DDD\Primitive;

use Yadddl\DDD\Error\InvalidValueObject;
use DateTimeImmutable;
use DateTimeInterface;
use JetBrains\PhpStorm\Pure;
use Stringable;

final class DateTime implements Stringable
{
    public const SIMPLE = 'Y-m-d H:i:s';

    public function __construct(private Date $date, private Time $time)
    {
    }

    #[Pure] public function __toString(): string
    {
        return "{$this->date} {$this->time}";
    }

    /**
     * @see toDateTimeImmutable
     */
    public function toDateTimeInterface(): DateTimeInterface
    {
        return $this->toDateTimeImmutable();
    }

    public function toDateTimeImmutable(): DateTimeImmutable
    {
        $dateTime = (string)$this;

        $result = DateTimeImmutable::createFromFormat(self::SIMPLE, $dateTime);

        assert($result !== false);

        return $result;
    }

    public static function createFromDateTimeInterface(DateTimeInterface $dateTime): DateTime
    {
        $date = Date::createFromDateTimeInterface($dateTime);
        $time = Time::createFromDateTimeInterface($dateTime);

        return new DateTime($date, $time);
    }

    public static function createFromFormat(string $dateTimeString, string $format): DateTime|InvalidValueObject
    {
        $dateTime = DateTimeImmutable::createFromFormat($format, $dateTimeString);

        if ($dateTime === false) {
            return new InvalidValueObject('invalid date time', "Invalid date time '{$dateTimeString}' provided for the format '{$format}'");
        }

        return self::createFromDateTimeInterface($dateTime);
    }

    public static function createFromString(string $dateTimeString): DateTime|InvalidValueObject
    {
        return self::createFromFormat($dateTimeString, self::SIMPLE);
    }
}
