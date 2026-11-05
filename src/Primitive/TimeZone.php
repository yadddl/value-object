<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use DateTimeZone as NativeTimeZone;
use Exception;
use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Validators\StringValue;
use Yadddl\ValueObject\Validators\ValidationContext;

final readonly class TimeZone implements Failable, ValidatedValue, \Stringable
{
    use FailableSuccess;

    /** @param non-empty-string $value */
    private function __construct(public string $value)
    {
    }

    public static function create(mixed $value): self|ErrorsBag
    {
        $context = self::validate($value);

        if (!$context->isValid()) {
            return $context->getErrors();
        }

        /** @var non-empty-string $identifier */
        $identifier = $context->getValue();

        return new self($identifier);
    }

    public static function validate(mixed $value): ValidationContext
    {
        return $value
            |> StringValue::from(...)
            |> StringValue::notEmpty('Timezone cannot be empty')
            |> self::isTimeZone('Invalid timezone identifier');
    }

    public static function utc(): self
    {
        return new self('UTC');
    }

    public static function fromDateTimeZone(NativeTimeZone $timeZone): self
    {
        /** @var non-empty-string $name */
        $name = $timeZone->getName();

        return new self($name);
    }

    public function toDateTimeZone(): NativeTimeZone
    {
        return new NativeTimeZone($this->value);
    }

    public function equals(self $other): bool
    {
        return $other->value === $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }

    private static function isTimeZone(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? 'Invalid timezone identifier';

        return static function (ValidationContext $context) use ($message) {
            if (!is_string($context->getValue())) {
                return $context;
            }

            return $context->validate(
                static function (string $value): bool {
                    try {
                        /** @var non-empty-string $value */
                        new NativeTimeZone($value);

                        return true;
                    } catch (Exception) {
                        return false;
                    }
                },
                $message,
            );
        };
    }
}
