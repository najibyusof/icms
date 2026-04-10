<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('level', 50);
            $table->unsignedTinyInteger('duration_semesters');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->unsignedTinyInteger('credit_hours');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['programme_id', 'is_active']);
        });

        Schema::create('academic_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('coordinator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 100);
            $table->unsignedSmallInteger('intake_year');
            $table->unsignedTinyInteger('semester');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['programme_id', 'name', 'intake_year', 'semester'], 'groups_programme_name_intake_sem_unique');
        });

        Schema::create('group_courses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')->constrained('academic_groups')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['group_id', 'course_id']);
        });

        Schema::create('examinations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('group_id')->constrained('academic_groups')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('title');
            $table->date('exam_date');
            $table->string('status', 30)->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'exam_date']);
        });

        Schema::create('workflow_instances', function (Blueprint $table): void {
            $table->id();
            $table->morphs('workflowable');
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('status', 30)->default('in_review');
            $table->unsignedTinyInteger('current_stage')->nullable();
            $table->timestamps();

            $table->index(['status', 'current_stage']);
        });

        Schema::create('workflow_approvals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role_name', 50);
            $table->unsignedTinyInteger('stage');
            $table->string('status', 30)->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->index(['workflow_instance_id', 'stage', 'status'], 'workflow_stage_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_approvals');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('examinations');
        Schema::dropIfExists('group_courses');
        Schema::dropIfExists('academic_groups');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('programmes');
    }
};
