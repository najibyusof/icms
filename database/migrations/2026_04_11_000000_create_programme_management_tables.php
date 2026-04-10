<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update programmes table to add new fields
        Schema::table('programmes', function (Blueprint $table): void {
            if (!Schema::hasColumn('programmes', 'programme_chair_id')) {
                $table->foreignId('programme_chair_id')
                    ->nullable()
                    ->after('is_active')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('programmes', 'description')) {
                $table->text('description')->nullable()->after('level');
            }
            if (!Schema::hasColumn('programmes', 'accreditation_body')) {
                $table->string('accreditation_body', 100)->nullable()->after('description');
            }
            if (!Schema::hasColumn('programmes', 'status')) {
                $table->string('status', 30)->default('draft')->after('is_active');
            }
        });

        // Programme Learning Objectives
        Schema::create('programme_plos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->cascadeOnDelete();
            $table->string('code', 30);
            $table->text('description');
            $table->unsignedTinyInteger('sequence_order');
            $table->timestamps();

            $table->unique(['programme_id', 'code']);
            $table->index(['programme_id']);
        });

        // Programme Educational Objectives
        Schema::create('programme_peos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->cascadeOnDelete();
            $table->string('code', 30);
            $table->text('description');
            $table->unsignedTinyInteger('sequence_order');
            $table->timestamps();

            $table->unique(['programme_id', 'code']);
            $table->index(['programme_id']);
        });

        // Programme Course Mapping
        Schema::create('programme_courses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedTinyInteger('year');
            $table->unsignedTinyInteger('semester');
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();

            $table->unique(['programme_id', 'course_id', 'year', 'semester']);
            $table->index(['programme_id', 'year', 'semester']);
        });

        // Study Plans
        Schema::create('study_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('total_years');
            $table->unsignedTinyInteger('semesters_per_year');
            $table->json('semesters_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['programme_id', 'name']);
            $table->index(['programme_id', 'is_active']);
        });

        // Study Plan Courses
        Schema::create('study_plan_courses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('study_plan_id')->constrained('study_plans')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedTinyInteger('year');
            $table->unsignedTinyInteger('semester');
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();

            $table->unique(['study_plan_id', 'course_id', 'year', 'semester']);
            $table->index(['study_plan_id', 'year', 'semester']);
        });

        // CLO to PLO Mapping (Course Learning Outcome to Programme Learning Outcome)
        Schema::create('clo_plo_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('programme_plo_id')->constrained('programme_plos')->cascadeOnDelete();
            $table->string('clo_code', 30);
            $table->text('alignment_notes')->nullable();
            $table->unsignedTinyInteger('bloom_level')->comment('1=Remember, 2=Understand, 3=Apply, 4=Analyze, 5=Evaluate, 6=Create');
            $table->timestamps();

            $table->unique(['course_id', 'programme_plo_id', 'clo_code']);
            $table->index(['course_id', 'programme_plo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clo_plo_mappings');
        Schema::dropIfExists('study_plan_courses');
        Schema::dropIfExists('study_plans');
        Schema::dropIfExists('programme_courses');
        Schema::dropIfExists('programme_peos');
        Schema::dropIfExists('programme_plos');

        Schema::table('programmes', function (Blueprint $table): void {
            if (Schema::hasColumn('programmes', 'programme_chair_id')) {
                $table->dropForeignKeyIfExists(['programme_chair_id_foreign']);
                $table->dropColumn('programme_chair_id');
            }
            if (Schema::hasColumn('programmes', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('programmes', 'accreditation_body')) {
                $table->dropColumn('accreditation_body');
            }
            if (Schema::hasColumn('programmes', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
