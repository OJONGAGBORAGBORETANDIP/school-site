<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Teacher can submit CA only or Exam only. Headteacher can approve/reject each with reason.
     */
    public function up(): void
    {
        Schema::table('subject_reports', function (Blueprint $table) {
            $table->timestamp('ca_submitted_at')->nullable()->after('submitted_at');
            $table->timestamp('exam_submitted_at')->nullable()->after('ca_submitted_at');
            $table->timestamp('ca_approved_at')->nullable()->after('exam_submitted_at');
            $table->timestamp('ca_rejected_at')->nullable()->after('ca_approved_at');
            $table->text('ca_rejection_reason')->nullable()->after('ca_rejected_at');
            $table->timestamp('exam_approved_at')->nullable()->after('ca_rejection_reason');
            $table->timestamp('exam_rejected_at')->nullable()->after('exam_approved_at');
            $table->text('exam_rejection_reason')->nullable()->after('exam_rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_reports', function (Blueprint $table) {
            $table->dropColumn([
                'ca_submitted_at',
                'exam_submitted_at',
                'ca_approved_at',
                'ca_rejected_at',
                'ca_rejection_reason',
                'exam_approved_at',
                'exam_rejected_at',
                'exam_rejection_reason',
            ]);
        });
    }
};
