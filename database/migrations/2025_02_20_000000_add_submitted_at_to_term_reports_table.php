<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Teachers can save scores as draft (submitted_at null) or submit for head teacher approval (submitted_at set).
     */
    public function up(): void
    {
        Schema::table('term_reports', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable()->after('is_approved_by_headteacher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('term_reports', function (Blueprint $table) {
            $table->dropColumn('submitted_at');
        });
    }
};
