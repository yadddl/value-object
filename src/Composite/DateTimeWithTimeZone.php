<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Composite;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone as NativeTimeZone;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Primitive\TimeZone;

final readonly class DateTimeWithTimeZone implements Failable, \Stringable
{
    use FailableSuccess;

    public const SIMPLE = 'Y-m-d H:i:s e';

    private function __construct(
        public Date $date,
        public Time $time,
        public TimeZone $timeZone,
    ) {
    }

    public static function now(string|NativeTimeZone|TimeZone|null $timeZone = null): self
    {
        $zone = self::resolveTimeZone($timeZone ?? date_default_timezone_get());

        return self::createFromDateTimeInterface(new DateTimeImmutable('now', $zone->toDateTimeZone()));
    }

    public static function create(string|DateTimeInterface|self $value): self|ErrorsBag
    {
        return match (true) {
            $value instanceof self => $value,
            $value instanceof DateTimeInterface => self::createFromDateTimeInterface($value),
            default => self::createFromString($value),
        };
    }

    public static function createFromDateTimeAndTimeZone(Date $date, Time $time, TimeZone $timeZone): self
    {
        return new self($date, $time, $timeZone);
    }

    public static function createFromDateTimeInterface(DateTimeInterface $dateTime): self
    {
        $nativeZone = $dateTime->getTimezone() ?: new NativeTimeZone('UTC');

        return new self(
            Date::createFromDateTimeInterface($dateTime),
            Time::createFromDateTimeInterface($dateTime),
            TimeZone::fromDateTimeZone($nativeZone),
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
        $result = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            "{$this->date} {$this->time}",
            $this->timeZone->toDateTimeZone(),
        );
        assert($result !== false);

        return $result;
    }

    public function withoutTimeZone(): DateTime
    {
        return DateTime::createFromDateAndTime($this->date, $this->time);
    }

    public function equals(self $other): bool
    {
        return $this->date->equals($other->date)
            && $this->time->equals($other->time)
            && $this->timeZone->equals($other->timeZone);
    }

    #[\Override]
    public function __toString(): string
    {
        return "{$this->date} {$this->time} {$this->timeZone}";
    }

    private static function resolveTimeZone(string|NativeTimeZone|TimeZone $timeZone): TimeZone
    {
        return match (true) {
            $timeZone instanceof TimeZone => $timeZone,
            $timeZone instanceof NativeTimeZone => TimeZone::fromDateTimeZone($timeZone),
            default => TimeZone::create($timeZone)->orFail(),
        };
    }
}
