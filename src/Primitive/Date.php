<?php

declare(strict_types=1);

namespace Yadddl\DDD\Primitive;

use Yadddl\DDD\Error\InvalidValueObject;
use DateTimeImmutable;
use DateTimeInterface;
use JetBrains\PhpStorm\Pure;
use Stringable;
use function sprintf;

final class Date implements Stringable
{
    private const FORMAT = 'Y-m-d';

    private int $days;

    private int $months;

    private int $years;

    private function __construct(int $days, int $months, int $years)
    {
        $this->days = $days;
        $this->months = $months;
        $this->years = $years;
    }

    public static function now(): Date
    {
        return self::createFromDateTimeInterface(new DateTimeImmutable());
    }

    /**
     * @psalm-pure
     */
    protected static function toString(int $years, int $months, int $days): string
    {
        return sprintf('%4d-%02d-%02d', $years, $months, $days);
    }

    #[Pure] public function __toString(): string
    {
        return self::toString($this->years, $this->months, $this->days);
    }

    public function toDateTimeImmutable(): DateTimeImmutable|false
    {
        $dateAsString = "{$this}T00:00:00Z";

        return DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339, $dateAsString);
    }

    #[Pure] public function toInt(): int
    {
        return (int)sprintf('%04d%02d%02d000000', $this->years, $this->months, $this->days);
    }

    public static function createFromDateTimeInterface(DateTimeInterface $date): Date
    {
        $days = (int)$date->format('d');
        $months = (int)$date->format('m');
        $years = (int)$date->format('Y');

        return new Date($days, $months, $years);
    }

    public static function createFromFormat(string $dateAsString, string $format): Date|InvalidValueObject
    {
        $date = DateTimeImmutable::createFromFormat($format, $dateAsString);

        if ($date === false) {
            return new InvalidValueObject('invalid date', "Invalid date '{$dateAsString}' provided for the format '{$format}'");
        }

        return self::createFromDateTimeInterface($date);
    }

    public static function createFromString(string $dateAsString): Date|InvalidValueObject
    {
        return self::createFromFormat($dateAsString, self::FORMAT);
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

    #[Pure] public function equalsTo(Date $date): bool
    {
        return $this->days === $date->days
            && $this->months === $date->months
            && $this->years === $date->years;
    }
}
