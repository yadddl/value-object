<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

interface Failable
{
    public function orFail(): static;
}
