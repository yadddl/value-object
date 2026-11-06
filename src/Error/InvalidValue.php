<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

/**
 * Generic field failure when only a message is known (built-in validators,
 * {@see ErrorsBag::fromMessages()}). Prefer a project-specific {@see InvalidField}
 * subclass when the domain has a clearer type.
 */
final class InvalidValue extends InvalidField
{
}
