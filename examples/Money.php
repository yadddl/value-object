<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Examples;

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Validators\ArrayValue;
use Yadddl\ValueObject\Validators\IntegerValue;
use Yadddl\ValueObject\Validators\StringValue;
use Yadddl\ValueObject\Validators\ValidationContext;

final readonly class Money implements Failable
{
    use FailableSuccess;

    private function __construct(
        public int      $amount,
        public Currency $currency
    ) {
    }

    public static function fromString(string $value): Money|ErrorsBag
    {
        /** @var ValidationContext $context */
        $context = $value
                |> StringValue::from(...)
                |> StringValue::notEmpty("Money string cannot be empty")
                |> StringValue::trim()  // Strip leading/trailing whitespace
                |> StringValue::regex(  // Extract amount and currency from the string
                    '/^(\d+(?:\.\d{1,2})?)([€$])$/u',
                    "Invalid money format. Expected format: '100.00€' or '100.00$'"
                )
                // Extract amount and currency from the regex match
                |> ArrayValue::mapKeys([1 => 'amount', 2 => 'currency'])
                |> ArrayValue::map(
                    fn (array $toParse) => [
                        // Parse amount to int and convert in cents
                        'amount' => Money::parseAmount($toParse['amount']),
                        // Parse currency to Currency Enum
                        'currency' => Money::parseCurrency($toParse['currency']),
                    ]
                )
                |> ArrayValue::validateArray( // Validate that currency is supported (very rare case)
                    fn (array $parsed) => $parsed['currency'] !== null,
                    "Unsupported currency symbol"
                )
                |> ArrayValue::map( // Create the Money object
                    fn (array $parsed) => self::create($parsed['amount'], $parsed['currency'])
                );

        return $context->isValid()
            ? $context->getValue()
            : $context->getErrors();
    }

    private static function parseAmount(string $amount): int
    {
        return (int)round((float)$amount * 100); // Convert to cents
    }

    private static function parseCurrency(string $currency): Currency|null
    {
        return match ($currency) {
            '€' => Currency::EUR,
            '$' => Currency::USD,
            default => null,
        };
    }

    public static function create(mixed $amount, Currency $currency): Money|ErrorsBag
    {
        $context = self::validate($amount);

        return $context->isValid()
            ? new self($context->getValue(), $currency)
            : $context->getErrors();
    }

    public static function validate(mixed $amount): IntegerValue
    {
        return $amount
                |> IntegerValue::from(...)
                |> IntegerValue::min(0, "Amount cannot be negative");
    }

    /**
     * Convert Money to string representation (e.g., "100.00€")
     */
    public function __toString(): string
    {
        // Convert cents to decimal (divide by 100)
        $decimalAmount = $this->amount / 100;

        // Format with 2 decimal places (standard for currency)
        $formatted = number_format($decimalAmount, 2, '.', '');

        return $formatted . $this->currency->value;
    }
}
