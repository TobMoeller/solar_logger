<?php

namespace App\Services\InverterCommander\Transformers;

interface TransformationStrategyContract
{
    public function transform(string $data): mixed;
}
