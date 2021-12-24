<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use DateTimeImmutable;
use DateTimeInterface;
use Yadddl\ValueObject\Error\InvalidValueObject;
use JetBrains\PhpStorm\Pure;

final class Time implements \Stringable
{
    private int $hours;

    private int $minutes;

    private int $seconds;

    private function __construct(int $hours, int $minutes, int $seconds)
    {
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
    }

    public function getHours(): int
    {
        return $this->hours;
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    #[Pure] private static function toString(int $hours, int $minutes, int $seconds): string
    {
        return \sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    #[Pure] public function __toString(): string
    {
        return self::toString($this->hours, $this->minutes, $this->seconds);
    }

    public static function createFromDateTimeInterface(DateTimeInterface $date): Time
    {
        $hours = (int)$date->format('H');
        $minutes = (int)$date->format('i');
        $seconds = (int)$date->format('s');

        return new Time($hours, $minutes, $seconds);
    }

    public static function fromString(string $time): Time|InvalidValueObject
    {
        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $time, $matches)) {
            return new InvalidValueObject('invalid time', "Invalid time provided: '{$time}'");
        }

        [, $hours, $minutes, $seconds] = $matches;

        return new Time((int)$hours, (int)$minutes, (int)$seconds);
    }

    #[Pure] public function toInt(): int
    {
        return (int)sprintf('%02d%02d%02d', $this->hours, $this->minutes, $this->seconds);
    }

    #[Pure] public function equalsTo(Time $time): bool
    {
        return $this->hours === $time->hours
            && $this->minutes === $time->minutes
            && $this->seconds === $time->seconds;
    }

    public static function now(): Time
    {
        return self::createFromDateTimeInterface(new DateTimeImmutable());
    }
}
