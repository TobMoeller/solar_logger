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
        Schema::table('inverters', function (Blueprint $table) {
            $table->boolean('is_monitored')
                ->default(true)
                ->after('port');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inverters', function (Blueprint $table) {
            $table->dropColumn('is_monitored');
        });
    }
};
