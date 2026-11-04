<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Composite;

use DateTimeImmutable;
use DateTimeInterface;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;

final readonly class DateTime implements Failable, \Stringable
{
    use FailableSuccess;

    public const SIMPLE = 'Y-m-d H:i:s';

    private function __construct(
        public Date $date,
        public Time $time,
    ) {
    }

    public static function now(): self
    {
        return new self(Date::now(), Time::now());
    }

    public static function create(string|DateTimeInterface|self $value): self|ErrorsBag
    {
        return match (true) {
            $value instanceof self => $value,
            $value instanceof DateTimeInterface => self::createFromDateTimeInterface($value),
            default => self::createFromString($value),
        };
    }

    public static function createFromDateTimeInterface(DateTimeInterface $dateTime): self
    {
        return new self(
            Date::createFromDateTimeInterface($dateTime),
            Time::createFromDateTimeInterface($dateTime),
        );
    }

    public static function createFromString(string $dateTimeString): self|ErrorsBag
    {
        return self::createFromFormat($dateTimeString, self::SIMPLE);
    }

    public static function createFromFormat(string $dateTimeString, string $format): self|ErrorsBag
    {
        $dateTime = DateTimeImmutable::createFromFormat('!'.$format, $dateTimeString);

        if ($dateTime === false || $dateTime->format($format) !== $dateTimeString) {
            return ErrorsBag::fromMessages("Invalid date time '{$dateTimeString}' provided for the format '{$format}'");
        }

        return self::createFromDateTimeInterface($dateTime);
    }

    public function format(string $format): string
    {
        return $this->toDateTimeImmutable()->format($format);
    }

    public function toDateTimeInterface(): DateTimeInterface
    {
        return $this->toDateTimeImmutable();
    }

    public function toDateTimeImmutable(): DateTimeImmutable
    {
        $result = DateTimeImmutable::createFromFormat(self::SIMPLE, (string) $this);
        assert($result !== false);

        return $result;
    }

    public function toInt(): int
    {
        return $this->date->toInt() + $this->time->toInt();
    }

    public function equals(self $other): bool
    {
        return $this->date->equals($other->date) && $this->time->equals($other->time);
    }

    #[\Override]
    public function __toString(): string
    {
        return "{$this->date} {$this->time}";
    }
}
