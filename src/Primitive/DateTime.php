<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use DateTimeImmutable;
use Yadddl\ValueObject\Error\InvalidValueObject;
use JetBrains\PhpStorm\Pure;

final class DateTime implements \Stringable
{
    public const SIMPLE = 'Y-m-d H:i:s';

    public function __construct(private Date $date, private Time $time)
    {
    }

    public function getDate(): Date
    {
        return $this->date;
    }

    public function getTime(): Time
    {
        return $this->time;
    }

    public static function now(): DateTime
    {
        return new DateTime(Date::now(), Time::now());
    }

    #[Pure] public function __toString(): string
    {
        return "{$this->date} {$this->time}";
    }

    /**
     * @see toDateTimeImmutable
     */
    public function toDateTimeInterface(): \DateTimeInterface
    {
        return $this->toDateTimeImmutable();
    }

    public function format(string $format): string
    {
        /** @var DateTimeImmutable $dateTime */
        $dateTime = $this->toDateTimeInterface();

        return $dateTime->format($format);
    }

    public function toDateTimeImmutable(): \DateTimeImmutable
    {
        $dateTime = (string)$this;

        $result = \DateTimeImmutable::createFromFormat(self::SIMPLE, $dateTime);

        assert($result !== false);

        return $result;
    }

    public static function createFromDateTimeInterface(\DateTimeInterface $dateTime): DateTime
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

    #[Pure] public function toInt(): int
    {
        return $this->date->toInt() + $this->time->toInt();
    }

    #[Pure] public function equalsTo(DateTime $value): bool
    {
        return $this->date->equalsTo($value->date)
            && $this->time->equalsTo($value->time);
    }

    public static function create(string|\DateTimeInterface|DateTime $value): DateTime|InvalidValueObject
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return self::createFromDateTimeInterface($value);
        }

        return self::createFromString($value);
    }

    #[Pure] public function toString(): string
    {
        return $this->__toString();
    }
}
