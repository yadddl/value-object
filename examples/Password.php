<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Examples;

use SensitiveParameter;
use Yadddl\ValueObject\Contracts\ValidatedValue;
use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Error\Failable;
use Yadddl\ValueObject\Error\FailableSuccess;
use Yadddl\ValueObject\Validators\StringValue;

final readonly class Password implements Failable, ValidatedValue
{
    use FailableSuccess;

    private function __construct(#[SensitiveParameter]public string $value)
    {
    }

    public static function create(#[SensitiveParameter] mixed $value): Password|ErrorsBag
    {
        $context = self::validate($value);

        return $context->isValid()
            ? new self(self::encrypt($context->getValue()))
            : $context->getErrors();
    }

    public static function validate(#[SensitiveParameter] mixed $value): StringValue
    {
        return $value
            |> StringValue::from(...)
            |> StringValue::minLength(8, "Password must be at least 8 characters long")
            |> StringValue::maxLength(20, "Password cannot exceed 20 characters")
            |> StringValue::hasUppercase("Password must contain at least one uppercase letter")
            |> StringValue::hasLowercase("Password must contain at least one lowercase letter")
            |> StringValue::hasNumber("Password must contain at least one number")
            |> StringValue::hasSpecialCharacter("Password must contain at least one special character");
    }

    /**
     * Encrypt the password using PHP's password_hash
     */
    private static function encrypt(#[SensitiveParameter] string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a plain password against the encrypted password
     */
    public function verify(#[SensitiveParameter] string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->value);
    }
}
