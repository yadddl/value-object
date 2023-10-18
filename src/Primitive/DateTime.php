<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use DateTimeImmutable;
use JetBrains\PhpStorm\Pure;
use Yadddl\ValueObject\Error\InvalidValueObject;

readonly class DateTime implements \Stringable
{
    public const SIMPLE = 'Y-m-d H:i:s';

    public function __construct(
        public Date $date,
        public Time $time
    ) {
    }

    public static function now(): DateTime
    {
        return new DateTime(Date::now(), Time::now());
    }

    public static function create(string|\DateTimeInterface|DateTime $value): DateTime|InvalidValueObject
    {
        return match (true) {
            $value instanceof self               => $value,
            $value instanceof \DateTimeInterface => self::createFromDateTimeInterface($value),
            default                              => self::createFromString($value)
        };
    }

    public static function createFromDateTimeInterface(\DateTimeInterface $dateTime): DateTime
    {
        $date = Date::createFromDateTimeInterface($dateTime);
        $time = Time::createFromDateTimeInterface($dateTime);

        return new DateTime($date, $time);
    }

    public static function createFromString(string $dateTimeString): DateTime|InvalidValueObject
    {
        return self::createFromFormat($dateTimeString, self::SIMPLE);
    }

    public static function createFromFormat(string $dateTimeString, string $format): DateTime|InvalidValueObject
    {
        $dateTime = DateTimeImmutable::createFromFormat($format, $dateTimeString);

        if ($dateTime === false) {
            return new InvalidValueObject("Invalid date time '{$dateTimeString}' provided for the format '{$format}'");
        }

        return self::createFromDateTimeInterface($dateTime);
    }

    public function format(string $format): string
    {
        /** @var DateTimeImmutable $dateTime */
        $dateTime = $this->toDateTimeInterface();

        return $dateTime->format($format);
    }

    /**
     * @see toDateTimeImmutable
     */
    public function toDateTimeInterface(): \DateTimeInterface
    {
        return $this->toDateTimeImmutable();
    }

    public function toDateTimeImmutable(): \DateTimeImmutable
    {
        $dateTime = (string)$this;

        $result = \DateTimeImmutable::createFromFormat(self::SIMPLE, $dateTime);

        assert($result !== false);

        return $result;
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

    #[Pure] public function toString(): string
    {
        return $this->__toString();
    }

    #[Pure] public function __toString(): string
    {
        return "{$this->date} {$this->time}";
    }
}
