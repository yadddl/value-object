<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Unit\Primitive;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Yadddl\ValueObject\Error\InvalidValueObject;
use Yadddl\ValueObject\Primitive\NotEmptyText;


it('should create from a factory', function () {
    $text = NotEmptyText::create('Hello, world!');

    expect($text)->toBeInstanceOf(NotEmptyText::class);
    expect((string)$text)->toBeString('Hello, world!');
});

it('should give an error in case of an empty string is given', function () {
    $text = NotEmptyText::create('');

    expect($text)->toBeInstanceOf(InvalidValueObject::class)
        ->getMessage()->toBe('The string should not be empty');
});


///**
// * @covers \Yadddl\ValueObject\Primitive\NotEmptyText
// */
//class NotEmptyTextTest extends TestCase
//{
//    public function testRight(): void {
//        $text = NotEmptyText::create('Hello, world!');
//
//        Assert::assertInstanceOf(NotEmptyText::class, $text);
//        Assert::assertSame('Hello, world!', (string)$text);
//    }
//
//    public function testLeft(): void {
//        $text = NotEmptyText::create('');
//
//        Assert::assertInstanceOf(InvalidValueObject::class, $text);
//        Assert::assertSame('The string should not be empty', $text->getMessage());
//    }
//}
