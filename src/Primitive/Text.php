<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use phpDocumentor\Reflection\Types\Static_;
use Yadddl\ValueObject\Error\InvalidString;
use Yadddl\ValueObject\Error\InvalidValueObject;
use function preg_match;

readonly class Text extends Primitive
{
    protected const REGEX = '/(.+)/';

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function toUpperCase(): static
    {
        $result = static::create(strtoupper((string)$this->value));

        assert($result instanceof static);

        return $result;
    }

    public function toLowerCase(): static
    {
        $result = static::create(strtolower((string)$this->value));

        assert($result instanceof static);

        return $result;
    }

    /**
     * @throws InvalidString
     */
    protected function validate(string|float|bool|int $value): void
    {
        /** @var string $regex */
        $regex = static::REGEX;

        $castedValue = $this->cast($value);

        if (preg_match($regex, $castedValue) !== 1) {
            throw new InvalidString("Invalid string: '{$castedValue}' does not match with '{$regex}'");
        }
    }

    protected function cast(float|bool|int|string $value): string
    {
        return (string)$value;
    }
}
