<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Composite;

use DateTimeImmutable;
use DateTimeInterface;
use JetBrains\PhpStorm\Pure;
use Yadddl\ValueObject\Error\InvalidValueObject;

readonly class Time implements \Stringable
{
    private function __construct(
        public int $hours,
        public int $minutes,
        public int $seconds
    ) {
    }

    public static function create(string $time): Time|InvalidValueObject
    {
        return Time::fromString($time);
    }

    public static function fromString(string $time): Time|InvalidValueObject
    {
        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $time, $matches)) {
            return new InvalidValueObject("Invalid time provided: '{$time}'");
        }

        [, $hours, $minutes, $seconds] = $matches;

        return new Time((int)$hours, (int)$minutes, (int)$seconds);
    }

    public static function now(): Time
    {
        return self::createFromDateTimeInterface(new DateTimeImmutable());
    }

    public static function createFromDateTimeInterface(DateTimeInterface $date): Time
    {
        $hours = (int)$date->format('H');
        $minutes = (int)$date->format('i');
        $seconds = (int)$date->format('s');

        return new Time($hours, $minutes, $seconds);
    }

    #[Pure] public function __toString(): string
    {
        return self::toString($this->hours, $this->minutes, $this->seconds);
    }

    #[Pure] private static function toString(int $hours, int $minutes, int $seconds): string
    {
        return \sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
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
}
