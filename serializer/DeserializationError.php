<?php

declare(strict_types=1);

namespace Yadddl\Serializer;

/**
 * Infrastructure / mapping failure — not a domain validation error.
 */
final class DeserializationError extends \LogicException
{
}
