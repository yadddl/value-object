<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Composite;

use DateTimeImmutable;
use DateTimeInterface;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;

final readonly class Date implements Failable, \Stringable
{
    use FailableSuccess;

    private const FORMAT = 'Y-m-d';

    private function __construct(
        public int $days,
        public int $months,
        public int $years,
    ) {
    }

    public static function now(): self
    {
        return self::createFromDateTimeInterface(new DateTimeImmutable());
    }

    public static function createFromDateTimeInterface(DateTimeInterface $date): self
    {
        return new self(
            (int) $date->format('d'),
            (int) $date->format('m'),
            (int) $date->format('Y'),
        );
    }

    public static function create(string|self|DateTimeInterface $value): self|ErrorsBag
    {
        return match (true) {
            $value instanceof self => self::createFrom($value->days, $value->months, $value->years),
            $value instanceof DateTimeInterface => self::createFromDateTimeInterface($value),
            default => self::fromString($value),
        };
    }

    public static function createFrom(int $days, int $months, int $years): self|ErrorsBag
    {
        $dateAsString = self::formatParts($years, $months, $days);
        $date = DateTimeImmutable::createFromFormat(self::FORMAT, $dateAsString);

        if ($date === false || $date->format(self::FORMAT) !== $dateAsString) {
            return ErrorsBag::fromMessages("Invalid date provided: '{$dateAsString}'");
        }

        return new self($days, $months, $years);
    }

    public static function createFromFormat(string $dateAsString, string $format): self|ErrorsBag
    {
        $date = DateTimeImmutable::createFromFormat('!'.$format, $dateAsString);

        if ($date === false || $date->format($format) !== $dateAsString) {
            return ErrorsBag::fromMessages("Invalid date '{$dateAsString}' provided for the format '{$format}'");
        }

        return self::createFromDateTimeInterface($date);
    }

    public static function fromString(string $dateAsString): self|ErrorsBag
    {
        return self::createFromFormat($dateAsString, self::FORMAT);
    }

    public function format(string $format): string
    {
        return $this->toDateTimeImmutable()->format($format);
    }

    public function toDateTimeImmutable(): DateTimeImmutable
    {
        $result = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339, "{$this}T00:00:00Z");
        assert($result !== false);

        return $result;
    }

    public function toInt(): int
    {
        return (int) sprintf('%04d%02d%02d000000', $this->years, $this->months, $this->days);
    }

    public function equals(self $other): bool
    {
        return $this->days === $other->days
            && $this->months === $other->months
            && $this->years === $other->years;
    }

    #[\Override]
    public function __toString(): string
    {
        return self::formatParts($this->years, $this->months, $this->days);
    }

    private static function formatParts(int $years, int $months, int $days): string
    {
        return sprintf('%4d-%02d-%02d', $years, $months, $days);
    }
}
