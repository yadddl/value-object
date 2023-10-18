<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use DateTimeImmutable;
use DateTimeInterface;
use JetBrains\PhpStorm\Pure;
use Yadddl\ValueObject\Error\InvalidValueObject;

readonly class Date implements \Stringable
{
    private const FORMAT = 'Y-m-d';

    private function __construct(
        public int $days,
        public int $months,
        public int $years
    ) {
    }

    public static function now(): Date
    {
        return self::createFromDateTimeInterface(new DateTimeImmutable());
    }

    public static function createFromDateTimeInterface(DateTimeInterface $date): Date
    {
        $days = (int)$date->format('d');
        $months = (int)$date->format('m');
        $years = (int)$date->format('Y');

        return new Date($days, $months, $years);
    }

    public function format(string $format): string
    {
        return $this->toDateTimeImmutable()->format($format);
    }

    public function toDateTimeImmutable(): DateTimeImmutable
    {
        $dateAsString = "{$this}T00:00:00Z";

        $result = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339, $dateAsString);

        assert($result !== false);

        return $result;
    }

    public static function createFromFormat(string $dateAsString, string $format): Date|InvalidValueObject
    {
        $date = DateTimeImmutable::createFromFormat($format, $dateAsString);

        if ($date === false) {
            return new InvalidValueObject('invalid date', "Invalid date '{$dateAsString}' provided for the format '{$format}'");
        }

        return self::createFromDateTimeInterface($date);
    }

    public static function create(string|self|DateTimeInterface $value): Date|InvalidValueObject
    {
        return match (true) {
            $value instanceof self              => self::createFrom($value->days, $value->months, $value->years),
            $value instanceof DateTimeInterface => self::createFromDateTimeInterface($value),
            default                             => self::fromString($value)
        };
    }

    public static function createFrom(int $days, int $months, int $years): Date|InvalidValueObject
    {
        $dateAsString = self::toString($years, $months, $days);

        $date = DateTimeImmutable::createFromFormat(self::FORMAT, $dateAsString);

        if ($date === false || $date->format(self::FORMAT) !== $dateAsString) {
            return new InvalidValueObject('invalid date', "Invalid date provided: '{$dateAsString}'");
        }

        return new Date($days, $months, $years);
    }

    /**
     * @psalm-pure
     */
    protected static function toString(int $years, int $months, int $days): string
    {
        return \sprintf('%4d-%02d-%02d', $years, $months, $days);
    }

    public static function fromString(string $dateAsString): Date|InvalidValueObject
    {
        return self::createFromFormat($dateAsString, self::FORMAT);
    }

    #[Pure]
    public function __toString(): string
    {
        return self::toString($this->years, $this->months, $this->days);
    }

    #[Pure]
    public function toInt(): int
    {
        return (int)sprintf('%04d%02d%02d000000', $this->years, $this->months, $this->days);
    }

    #[Pure]
    public function equalsTo(Date $date): bool
    {
        return $this->days === $date->days
            && $this->months === $date->months
            && $this->years === $date->years;
    }
}
