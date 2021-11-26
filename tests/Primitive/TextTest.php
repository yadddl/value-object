<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Yadddl\ValueObject\Error\InvalidValueObject;
use Yadddl\ValueObject\Error\ValidationError;

/**
 * @covers \Yadddl\ValueObject\Primitive\Text
 */
class TextTest extends TestCase
{
    public function testRight(): void
    {
        $text = Text::create("Pippo");

        Assert::assertSame('Pippo', (string)$text);
        Assert::assertSame('PIPPO', (string)$text->toUpperCase());
        Assert::assertSame('pippo', (string)$text->toLowerCase());
        Assert::assertTrue($text->equalsTo(Text::create('Pippo')));
    }

    public function testExtension(): void
    {
        $rightText = ExampleText::create('testString');
        $wrongText = ExampleText::create('wrong text');

        Assert::assertInstanceOf(ExampleText::class, $rightText);
        Assert::assertInstanceOf(InvalidValueObject::class, $wrongText);
        Assert::assertSame('invalid string', $wrongText->getType());
        Assert::assertSame('Invalid string: \'wrong text\' does not match with \'/^test/\'', $wrongText->getMessage());
    }
}

class ExampleText extends Text {
    protected string $regex = '/^test/';
}
