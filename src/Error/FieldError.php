<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

use JetBrains\PhpStorm\Pure;

final readonly class FieldError
{
    public function __construct(
        public string $key,
        public string $message,
        public ?\Throwable $previous = null
    ) {}

    #[Pure] public function addPrefix(string $prefix): FieldError
    {
        $newKey = sprintf("%s.%s", $prefix, $this->key);

        return new FieldError($newKey, $this->message, $this->previous);
    }
}
