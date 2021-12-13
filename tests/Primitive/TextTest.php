<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Yadddl\ValueObject\Error\InvalidValueObject;

/**
 * @covers \Yadddl\ValueObject\Primitive\Text
 */
class TextTest extends TestCase
{
    public function testRight(): void
    {
        $text = Text::create("Pippo");

        Assert::assertInstanceOf(Text::class, $text);
        Assert::assertSame('Pippo', (string)$text);
        Assert::assertSame('PIPPO', (string)$text->toUpperCase());
        Assert::assertSame('pippo', (string)$text->toLowerCase());

        $text2 = Text::create('Pippo');
        Assert::assertInstanceOf(Text::class, $text2);
        Assert::assertTrue($text->equalsTo($text2));
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
