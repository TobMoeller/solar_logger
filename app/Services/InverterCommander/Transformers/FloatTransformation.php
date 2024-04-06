<?php

namespace App\Services\InverterCommander\Transformers;

class FloatTransformation implements TransformationStrategyContract
{
    public function transform(string $data): float
    {
        return floatval($data);
    }
}
