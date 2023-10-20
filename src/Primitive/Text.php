<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use phpDocumentor\Reflection\Types\Static_;
use Yadddl\ValueObject\Error\InvalidString;
use Yadddl\ValueObject\Error\InvalidValueObject;

use function preg_match;

readonly class Text
{
    /** @var string  */
    protected const REGEX = '/(.+)/';

    final public function __construct(public string $value)
    {
        $this->validate($value);
    }


    public function __toString(): string
    {
        return $this->value;
    }

    public function toUpperCase(): static
    {
        return new static(strtoupper($this->value));
    }

    public function toLowerCase(): static
    {
        return new static(strtolower($this->value));
    }

    /**
     * @throws InvalidString
     */
    protected function validate(string $value): void
    {
        /** @psalm-var non-empty-string $regex
         */
        $regex = static::REGEX;

        if (preg_match($regex, $value) !== 1) {
            throw new InvalidString("Invalid string: '{$value}' does not match with '{$regex}'", 'value');
        }
    }

    public function equals(Text $object): bool
    {
        return $object->value === $this->value;
    }
}
