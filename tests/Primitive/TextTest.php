<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Primitive\NotEmptyText;
use Yadddl\ValueObject\Primitive\Text;

test('creates valid text', function () {
    $text = Text::create('hello');

    expect($text)->toBeInstanceOf(Text::class)
        ->and($text->value)->toBe('hello')
        ->and((string) $text)->toBe('hello');
});

test('rejects empty text for base Text regex that still allows empty via (.+)', function () {
    // REGEX /(.+)/ requires at least one character in a capturing group; empty fails
    $result = Text::create('');

    expect($result)->toBeInstanceOf(ErrorsBag::class);
});

test('case transformations return new instances', function () {
    $text = Text::create('Hi')->orFail();

    expect($text->toUpperCase()->value)->toBe('HI')
        ->and($text->toLowerCase()->value)->toBe('hi')
        ->and($text->value)->toBe('Hi');
});

test('equals compares values of the same type', function () {
    $a = Text::create('x')->orFail();
    $b = Text::create('x')->orFail();
    $c = Text::create('y')->orFail();

    expect($a->equals($b))->toBeTrue()
        ->and($a->equals($c))->toBeFalse();
});

test('not empty text rejects empty string', function () {
    expect(NotEmptyText::create(''))->toBeInstanceOf(ErrorsBag::class)
        ->and(NotEmptyText::create('ok'))->toBeInstanceOf(NotEmptyText::class);
});

test('text orFail returns self when valid', function () {
    $text = Text::create('ok')->orFail();

    expect($text)->toBeInstanceOf(Text::class);
});
