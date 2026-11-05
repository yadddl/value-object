<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

trait FailableSuccess
{
    #[\Override]
    public function orFail(): static
    {
        return $this;
    }
}
