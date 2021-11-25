<?php

declare(strict_types=1);

namespace Yadddl\DDD\Primitive;

use Yadddl\DDD\Error\InvalidValueObject;
use DateTimeImmutable;
use DateTimeInterface;
use JetBrains\PhpStorm\Pure;
use Stringable;
use function sprintf;

final class Time implements Stringable
{
    private const FORMAT = 'H:i:s';

    private int $hours;

    private int $minutes;

    private int $seconds;

    private function __construct(int $hours, int $minutes, int $seconds)
    {
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
    }

    #[Pure] private static function toString(int $hours, int $minutes, int $seconds): string
    {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
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
