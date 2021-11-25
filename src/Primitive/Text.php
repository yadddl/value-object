<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use Yadddl\ValueObject\Error\InvalidString;
use Yadddl\ValueObject\Error\InvalidValueObject;
use JetBrains\PhpStorm\Pure;
use Stringable;
use function preg_match;

class Text implements Stringable
{
    private string $value;

    protected string $regex = '/(.+)/';

    final public function __construct(string $value)
    {
        $this->validate($value);

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function validate(string $value): void
    {
        if (preg_match($this->regex, $value) !== 1) {
            throw new InvalidString($this->regex, $value);
        }
    }

    final public static function create(string|Stringable $value): static|InvalidValueObject
    {
        try {
            return new static((string)$value);
        } catch (InvalidString $exception) {
            return new InvalidValueObject('invalid string', $exception->getMessage());
        }
    }

    #[Pure] public function equalsTo(Text $value): bool
    {
        return $this->value === $value->value;
    }

    public function toUpper(): static
    {
        return new static(strtoupper($this->value));
    }
}
