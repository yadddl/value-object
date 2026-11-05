<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

/**
 * Common parent for domain-level failures thrown at boundaries (e.g. {@see Failable::orFail()}).
 *
 * Catch this to handle application/domain errors without conflating them with infra / PHP builtins.
 * Validation input failures use {@see ValidationFailedException}; post-validation invariants use
 * {@see DomainLogicException}.
 */
abstract class DomainException extends \DomainException
{
}
