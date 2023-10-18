<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Unit\Primitive;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Yadddl\ValueObject\Error\InvalidValueObject;
use Yadddl\ValueObject\Primitive\Text;

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
        Assert::assertObjectEquals($text2, $text);
    }

    public function testExtension(): void
    {
        $rightText = ExampleText::create('testString');
        $wrongText = ExampleText::create('wrong text');

        Assert::assertInstanceOf(ExampleText::class, $rightText);
        Assert::assertInstanceOf(InvalidValueObject::class, $wrongText);
        Assert::assertSame('Invalid string: \'wrong text\' does not match with \'/^test/\'', $wrongText->getMessage());
    }
}

readonly class ExampleText extends Text {
    protected const REGEX = '/^test/';
}
