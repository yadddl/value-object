<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Primitive\Url;

test('creates valid url', function () {
    $url = Url::create('https://example.com/path');

    expect($url)->toBeInstanceOf(Url::class)
        ->and($url->value)->toBe('https://example.com/path')
        ->and((string) $url)->toBe('https://example.com/path');
});

test('rejects invalid url', function () {
    expect(Url::create('not-a-url'))->toBeInstanceOf(ErrorsBag::class)
        ->and(Url::create(123))->toBeInstanceOf(ErrorsBag::class);
});

test('equals compares url values', function () {
    $a = Url::create('https://example.com')->orFail();
    $b = Url::create('https://example.com')->orFail();
    $c = Url::create('https://other.test')->orFail();

    expect($a->equals($b))->toBeTrue()
        ->and($a->equals($c))->toBeFalse();
});
