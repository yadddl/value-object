<?php

namespace Yadddl\ValueObject\Unit\Examples;

use Yadddl\ValueObject\Attributes\Collection;
use Yadddl\ValueObject\Primitive\NotEmptyText;
use Yadddl\ValueObject\Primitive\PositiveInteger;

final readonly class IterableCompositeObject
{
    /**
     * @param NotEmptyText $name
     * @param PositiveInteger $age
     * @param SimpleCompositeObject[] $children
     */
    public function __construct(
        public NotEmptyText    $name,
        public PositiveInteger $age,

        #[Collection(SimpleCompositeObject::class)]
        public array $children
    ) {}
}