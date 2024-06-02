<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('export_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->nullable();
            $table->timestamp('exported_at')->nullable();
            $table->foreignId('exportable_id');
            $table->string('exportable_type');
            $table->timestamps();

            $table->unique(['exportable_id', 'exportable_type'], 'exportable_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_entries');
    }
};
