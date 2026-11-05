<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Examples;

use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Error\MultipleFieldErrorsBag;
use Yadddl\ValueObject\Validators\BackedEnumValue;
use Yadddl\ValueObject\Validators\MultipleValidationContext;

/**
 * Composite that keeps a backed enum case from {@see BackedEnumValue}.
 */
final readonly class Order implements Failable
{
    use FailableSuccess;

    private function __construct(
        public string $reference,
        public OrderStatus $status,
    ) {
    }

    public static function create(
        string $reference,
        mixed $status,
    ): self|MultipleFieldErrorsBag {
        $context = MultipleValidationContext::setup(
            reference: Name::create($reference),
            status: BackedEnumValue::create(OrderStatus::class, $status),
        );

        return $context->isValid()
            ? new self(...$context->getValues())
            : $context->getErrors();
    }
}
