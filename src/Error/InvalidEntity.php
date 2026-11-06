<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

/**
 * Entity / aggregate invariant failure after individual values are valid
 * (illegal state, lifecycle rule, uniqueness conflict expressed as domain error, …).
 *
 * Extend with a project-specific type (e.g. AccountCannotBeApproved).
 */
abstract class InvalidEntity extends DomainError
{
}
