<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Primitive\Email;

test('creates valid email', function () {
    $email = Email::create('user@example.com');

    expect($email)->toBeInstanceOf(Email::class)
        ->and($email->value)->toBe('user@example.com')
        ->and((string) $email)->toBe('user@example.com');
});

test('rejects invalid email', function () {
    expect(Email::create('not-an-email'))->toBeInstanceOf(ErrorsBag::class);
});

test('equals compares email values', function () {
    $a = Email::create('a@example.com')->orFail();
    $b = Email::create('a@example.com')->orFail();
    $c = Email::create('b@example.com')->orFail();

    expect($a->equals($b))->toBeTrue()
        ->and($a->equals($c))->toBeFalse();
});
