<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

trait FailableTrait
{
    /**
     * @throws ValidationError
     */
    public function orFail(): static
    {
        if ($this instanceof ValidationError) {
            throw $this;
        }

        return $this;
    }
}
