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
        // Basic role system for users (admin, headteacher, teacher, parent)
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // admin, headteacher, teacher, parent
            $table->string('label')->nullable(); // Human readable
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role_id', 'user_id']);
        });

        // School years (e.g. 2025-2026)
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. 2025-2026
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        // Terms: 1st, 2nd, 3rd per school year
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('number'); // 1,2,3
            $table->string('name'); // 1st Term, 2nd Term, 3rd Term
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->date('results_published_at')->nullable();
            $table->timestamps();
            $table->unique(['school_year_id', 'number']);
        });

        // Classes P1 - P6; sections/streams like P1A etc.
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Primary 1, Primary 2...
            $table->string('code')->unique(); // P1, P2...
            $table->unsignedTinyInteger('level'); // 1-6
            $table->timestamps();
        });

        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // A, B, etc.
            $table->string('label')->nullable(); // P1A, P1B
            $table->unsignedInteger('capacity')->nullable();
            $table->foreignId('class_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['school_class_id', 'name']);
        });

        // Subjects offered in school, can be scoped by class level
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Mathematics
            $table->string('code')->unique(); // MATH
            $table->unsignedTinyInteger('min_level')->default(1); // P1
            $table->unsignedTinyInteger('max_level')->default(6); // P6
            $table->boolean('is_compulsory')->default(true);
            $table->timestamps();
        });

        // Which teacher teaches which subject in which section
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['teacher_id', 'class_section_id', 'subject_id'], 'unique_teacher_class_subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('class_sections');
        Schema::dropIfExists('school_classes');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('school_years');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};

