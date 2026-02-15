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
        // Grading scale for marks to grades/remarks
        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('min_mark');
            $table->unsignedTinyInteger('max_mark');
            $table->string('grade'); // A, B, etc.
            $table->string('remark'); // Excellent, Very Good...
            $table->timestamps();
        });

        // Attendance per student per term
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late']);
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->unique(['enrollment_id', 'term_id', 'date'], 'unique_attendance_per_day');
        });

        // Term report per student (overall)
        Schema::create('term_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->decimal('average', 5, 2)->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->decimal('class_average', 5, 2)->nullable();
            $table->unsignedInteger('class_size')->nullable();
            $table->text('class_teacher_remark')->nullable();
            $table->text('headteacher_remark')->nullable();
            $table->boolean('is_approved_by_headteacher')->default(false);
            $table->timestamps();
            $table->unique(['enrollment_id', 'term_id']);
        });

        // Subject-level marks inside a term report
        Schema::create('subject_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->decimal('ca_mark', 5, 2)->nullable(); // continuous assessment
            $table->decimal('exam_mark', 5, 2)->nullable();
            $table->decimal('total_mark', 5, 2)->nullable();
            $table->string('grade')->nullable();
            $table->string('remark')->nullable();
            $table->text('teacher_comment')->nullable();
            $table->timestamps();
            $table->unique(['term_report_id', 'subject_id'], 'unique_subject_report');
        });

        // Behaviour/skills ratings per term
        Schema::create('behaviour_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_report_id')->constrained()->cascadeOnDelete();
            $table->string('aspect'); // e.g. Punctuality, Discipline
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->string('comment')->nullable();
            $table->timestamps();
        });

        // End-of-year promotion decisions
        Schema::create('promotion_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_promoted')->default(false);
            $table->string('next_class_label')->nullable(); // e.g. Primary 4
            $table->text('decision_note')->nullable();
            $table->timestamps();
            $table->unique(['enrollment_id', 'school_year_id'], 'unique_promotion_decision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_decisions');
        Schema::dropIfExists('behaviour_ratings');
        Schema::dropIfExists('subject_reports');
        Schema::dropIfExists('term_reports');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('grading_scales');
    }
};

