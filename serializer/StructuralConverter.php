<?php

declare(strict_types=1);

namespace Yadddl\Serializer;

use Yadddl\ValueObject\Composite\Date;
use Yadddl\ValueObject\Composite\DateTime;
use Yadddl\ValueObject\Composite\DateTimeWithTimeZone;
use Yadddl\ValueObject\Composite\Time;

/**
 * Convenience facade for structural object ↔ array conversion.
 */
final class StructuralConverter
{
    public function __construct(
        private Serializer $serializer = new StructuralSerializer(),
        private Deserializer $deserializer = new FactoryDeserializer(),
    ) {
    }

    /**
     * Converter with Date/Time/DateTime(/WithTimeZone) serialized as strings for natural round-trips.
     */
    public static function create(): self
    {
        /** @var array<class-string, callable(object): mixed> $typeSerializers */
        $typeSerializers = [
            Date::class => static fn (Date $date): string => (string) $date,
            Time::class => static fn (Time $time): string => (string) $time,
            DateTime::class => static fn (DateTime $dateTime): string => (string) $dateTime,
            DateTimeWithTimeZone::class => static fn (DateTimeWithTimeZone $dateTime): string => (string) $dateTime,
        ];

        return new self(
            new StructuralSerializer($typeSerializers),
            new FactoryDeserializer(),
        );
    }

    public function toArray(mixed $value): mixed
    {
        return $this->serializer->serialize($value);
    }

    /**
     * @param class-string $type
     */
    public function fromArray(mixed $data, string $type): mixed
    {
        return $this->deserializer->deserialize($data, $type);
    }

    public function serializer(): Serializer
    {
        return $this->serializer;
    }

    public function deserializer(): Deserializer
    {
        return $this->deserializer;
    }
}
