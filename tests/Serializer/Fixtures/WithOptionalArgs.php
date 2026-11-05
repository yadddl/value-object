<?php

declare(strict_types=1);

namespace Tests\Serializer\Fixtures;

readonly final class WithOptionalArgs
{
    private function __construct(
        public string $name,
        public ?string $note,
        public string $flag,
    ) {
    }

    public static function create(string $name, ?string $note = null, string $flag = 'default'): self
    {
        return new self($name, $note, $flag);
    }

    public static function createRequiredNote(string $name, string $note): self
    {
        return new self($name, $note, 'default');
    }
}
