<?php

declare(strict_types=1);

namespace Tests\Serializer\Fixtures;

final class WithStaticProperty
{
    public static string $staticValue = 'skip-me';

    public function __construct(public string $name)
    {
    }
}
