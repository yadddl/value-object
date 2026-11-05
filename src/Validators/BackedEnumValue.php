<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Validators;

use BackedEnum;
use ReflectionEnum;
use TypeError;
use Yadddl\ValueObject\Error\ErrorsBag;

/**
 * Domain-side helper: turn a raw string/int (or existing case) into a BackedEnum without throwing.
 *
 * Use with {@see MultipleValidationContext} — successful results keep the enum instance.
 */
final class BackedEnumValue
{
    /**
     * @template T of BackedEnum
     *
     * @param class-string<T> $enumClass
     *
     * @return T|ErrorsBag
     */
    public static function create(
        string $enumClass,
        mixed $value,
        ?string $invalidMessage = null,
    ): BackedEnum|ErrorsBag {
        if (!enum_exists($enumClass)) {
            return ErrorsBag::fromMessages(sprintf(
                'Expected a backed enum class, got "%s"',
                $enumClass,
            ));
        }

        $reflection = new ReflectionEnum($enumClass);

        if (!$reflection->isBacked()) {
            return ErrorsBag::fromMessages(sprintf(
                'Expected a backed enum class, got unit enum "%s"',
                $reflection->getShortName(),
            ));
        }

        /** @var class-string<T> $enumClass */
        if ($value instanceof $enumClass) {
            return $value;
        }

        $message = $invalidMessage ?? sprintf('Invalid value for %s', $reflection->getShortName());

        if (!is_string($value) && !is_int($value)) {
            return ErrorsBag::fromMessages($message);
        }

        try {
            /** @var T|null $case */
            $case = $enumClass::tryFrom($value);
        } catch (TypeError) {
            return ErrorsBag::fromMessages($message);
        }

        return $case ?? ErrorsBag::fromMessages($message);
    }
}
