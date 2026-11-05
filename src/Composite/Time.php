<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Composite;

use DateTimeImmutable;
use DateTimeInterface;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;

final readonly class Time implements Failable, \Stringable
{
    use FailableSuccess;

    private function __construct(
        public int $hours,
        public int $minutes,
        public int $seconds,
    ) {
    }

    public static function create(string $time): self|ErrorsBag
    {
        return self::fromString($time);
    }

    public static function createFrom(int $hours, int $minutes, int $seconds): self|ErrorsBag
    {
        return self::fromString(sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds));
    }

    public static function fromString(string $time): self|ErrorsBag
    {
        if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d):([0-5]\d)$/', $time, $matches) !== 1) {
            return ErrorsBag::fromMessages("Invalid time provided: '{$time}'");
        }

        return new self((int) $matches[1], (int) $matches[2], (int) $matches[3]);
    }

    public static function now(): self
    {
        return self::createFromDateTimeInterface(new DateTimeImmutable());
    }

    public static function createFromDateTimeInterface(DateTimeInterface $date): self
    {
        return new self(
            (int) $date->format('H'),
            (int) $date->format('i'),
            (int) $date->format('s'),
        );
    }

    public function toInt(): int
    {
        return (int) sprintf('%02d%02d%02d', $this->hours, $this->minutes, $this->seconds);
    }

    public function equals(self $other): bool
    {
        return $this->hours === $other->hours
            && $this->minutes === $other->minutes
            && $this->seconds === $other->seconds;
    }

    #[\Override]
    public function __toString(): string
    {
        return sprintf('%02d:%02d:%02d', $this->hours, $this->minutes, $this->seconds);
    }
}
