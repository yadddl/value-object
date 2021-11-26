<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Yadddl\ValueObject\Error\InvalidValueObject;

/**
 * @covers \Yadddl\ValueObject\Primitive\NotEmptyText
 */
class NotEmptyTextTest extends TestCase
{
    public function testRight(): void {
        $text = NotEmptyText::create('Hello, world!');

        Assert::assertInstanceOf(NotEmptyText::class, $text);
        Assert::assertSame('Hello, world!', (string)$text);
    }

    public function testLeft(): void {
        $text = NotEmptyText::create('');

        Assert::assertInstanceOf(InvalidValueObject::class, $text);
        Assert::assertSame('The string should not be empty', $text->getMessage());
    }
}
