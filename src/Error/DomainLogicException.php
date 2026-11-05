<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

/**
 * Domain invariant / business-rule failure after (or aside from) input validation.
 *
 * Prefer {@see ErrorsBag} for invalid user input. Use this (or a subclass) when the system
 * state or command is illegal even though individual values were already valid —
 * e.g. "cannot close an already closed order".
 */
class DomainLogicException extends DomainException
{
}
