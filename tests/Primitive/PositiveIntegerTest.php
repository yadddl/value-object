<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Primitive;

use Yadddl\ValueObject\Error\InvalidValueObject;
use Yadddl\ValueObject\Serializer\SerializerBaseFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Yadddl\ValueObject\Primitive\PositiveInteger
 */
class PositiveIntegerTest extends TestCase
{
    public function testRight(): void
    {
        $positive = PositiveInteger::create(10);

        Assert::assertInstanceOf(PositiveInteger::class, $positive);
    }

    public function testLeft(): void
    {
        $error = PositiveInteger::create(-20);

        Assert::assertInstanceOf(InvalidValueObject::class, $error);
        Assert::assertSame('integer too small', $error->getType());
        Assert::assertSame( 'The value -20 is too small. Minimum 0', $error->getMessage());
    }

    public function testSerialization(): void
    {
        $serializer = SerializerBaseFactory::make();

        $positive = PositiveInteger::create(10);

        $value = $serializer->serialize($positive);

        Assert::assertSame(10, $value);
    }
}
