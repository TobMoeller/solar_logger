<?php

use App\Services\InverterCommander\Transformers\DivideFloatByTenTransformation;
use App\Services\InverterCommander\Transformers\FloatTransformation;

it('transforms to float', function () {
    expect(app(FloatTransformation::class)->transform('123'))
        ->toBe(123.0);
});

it('transforms to float and divides by ten', function () {
    expect(app(DivideFloatByTenTransformation::class)->transform('123'))
        ->toBe(12.3);
});
