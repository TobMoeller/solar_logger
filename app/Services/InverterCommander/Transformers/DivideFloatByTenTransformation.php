<?php

namespace App\Services\InverterCommander\Transformers;

class DivideFloatByTenTransformation implements TransformationStrategyContract
{
    public function transform(string $data): float
    {
        return floatval($data) / 10.0;
    }
}
