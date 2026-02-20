<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Only one term should be active at a time; teachers enter marks only for the active term.
     */
    public function up(): void
    {
        Schema::table('terms', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('results_published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terms', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
