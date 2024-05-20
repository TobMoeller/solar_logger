<?php

namespace Modules\Export\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Export\Models\ExportEntry;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<ExportEntry>
 */
class ExportEntryFactory extends Factory
{
    protected $model = ExportEntry::class;

    public function configure(): static
    {
        return $this->afterMaking(function (ExportEntry $exportEntry) {
            if (empty($exportEntry->exportable_id)) {
                $relatedModel = (fake()->randomElement(ExportEntry::exportables()))::factory()->create();
                $exportEntry->exportable_id = $relatedModel->id;
                $exportEntry->exportable_type = $relatedModel::class;
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_id' => fake()->unique()->numberBetween(1, 999_999),
            'exported_at' => fake()->dateTime(),
        ];
    }
}
