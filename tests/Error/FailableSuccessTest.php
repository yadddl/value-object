<?php

declare(strict_types=1);

use Tests\Support\SampleAge;

test('successful value object orFail returns itself', function () {
    $age = SampleAge::create(30);

    expect($age)->toBeInstanceOf(SampleAge::class)
        ->and($age->orFail())->toBe($age)
        ->and($age->orFail()->value)->toBe(30);
});
