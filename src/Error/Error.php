<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Error;

// Composer may autoload this file when code still references Error.
class_exists(DomainError::class);

/**
 * @deprecated Use {@see DomainError} instead.
 */
class_alias(DomainError::class, __NAMESPACE__.'\\Error');
