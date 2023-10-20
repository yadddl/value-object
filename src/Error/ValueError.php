<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

use Throwable;
use ValueError as NativeValueError;

abstract class ValueError extends NativeValueError
{
    public function __construct(
        string        $message,
        public string $field,
        ?Throwable    $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
