<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Unit\Primitive;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Yadddl\ValueObject\Error\InvalidValueObject;
use Yadddl\ValueObject\Primitive\PositiveInteger;

/**
 * @covers \Yadddl\ValueObject\Primitive\PositiveInteger
 */
class PositiveIntegerTest extends TestCase
{
    public static function rightDataProvider(): \Generator
    {
        yield 'integer' => [10, 10];
        yield 'string 1' => ["10", 10];
        yield 'string 2' => [" 10 ", 10];
    }

    /**
     * @dataProvider rightDataProvider
     */
    public function testRight(string|int $value, int $expectedResult): void
    {
        $positive = PositiveInteger::create($value);

        Assert::assertInstanceOf(PositiveInteger::class, $positive);
        Assert::assertSame($expectedResult, $positive->value);
        Assert::assertSame((string)$expectedResult, (string)$positive);
        Assert::assertTrue($positive->equalsTo($positive));
        Assert::assertTrue($positive->equalsTo(PositiveInteger::create($expectedResult)));
    }

    public function testLeft(): void
    {
        $error = PositiveInteger::create(-20);

        Assert::assertInstanceOf(InvalidValueObject::class, $error);
        Assert::assertSame('integer too small', $error->type);
        Assert::assertSame( 'The value -20 is too small. Minimum 0', $error->getMessage());
    }
}
