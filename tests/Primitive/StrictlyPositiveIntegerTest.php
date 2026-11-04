<?php

declare(strict_types=1);

use Yadddl\ValueObject\Error\ErrorsBag;
use Yadddl\ValueObject\Primitive\StrictlyPositiveInteger;

test('strictly positive integer rejects zero and negatives', function () {
    expect(StrictlyPositiveInteger::create(1))->toBeInstanceOf(StrictlyPositiveInteger::class)
        ->and(StrictlyPositiveInteger::create(0))->toBeInstanceOf(ErrorsBag::class)
        ->and(StrictlyPositiveInteger::create(-3))->toBeInstanceOf(ErrorsBag::class);
});
