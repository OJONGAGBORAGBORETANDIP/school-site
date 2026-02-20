<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Submission is per subject: when teacher submits, only this subject's scores are marked submitted.
     */
    public function up(): void
    {
        Schema::table('subject_reports', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable()->after('teacher_comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_reports', function (Blueprint $table) {
            $table->dropColumn('submitted_at');
        });
    }
};
