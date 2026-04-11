<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── jsu ─────────────────────────────────────────────────────────────
        if (!Schema::hasTable('jsu')) {
            Schema::create('jsu', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('academic_session', 30);                 // e.g. "2024/2025-1"
                $table->string('exam_type', 30);                        // midterm|final|quiz|test
                $table->string('title');
                $table->unsignedSmallInteger('total_questions')->default(0);
                $table->unsignedSmallInteger('total_marks')->default(100);
                $table->unsignedSmallInteger('duration_minutes')->nullable();
                $table->string('status', 30)->default('draft');         // draft|submitted|approved|rejected|active
                $table->json('difficulty_config')->nullable();           // per-JSU override of global defaults
                $table->text('notes')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('activated_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['course_id', 'status']);
                $table->index(['academic_session', 'exam_type']);
            });
        }

        // ── jsu_blueprints ───────────────────────────────────────────────────
        if (!Schema::hasTable('jsu_blueprints')) {
            Schema::create('jsu_blueprints', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('jsu_id')->constrained('jsu')->cascadeOnDelete();
                $table->foreignId('clo_id')->nullable()->constrained('course_clos')->nullOnDelete();
                $table->unsignedSmallInteger('question_no');
                $table->string('topic', 200)->nullable();
                $table->unsignedTinyInteger('bloom_level');              // 1=Remember … 6=Create
                $table->decimal('marks', 6, 2)->default(0);
                $table->decimal('weight_percentage', 5, 2)->nullable(); // computed or set manually
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['jsu_id', 'question_no']);
                $table->index(['jsu_id', 'bloom_level']);
            });
        }

        // ── jsu_logs ─────────────────────────────────────────────────────────
        if (!Schema::hasTable('jsu_logs')) {
            Schema::create('jsu_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('jsu_id')->constrained('jsu')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users');
                $table->string('action', 50);                           // created|submitted|approved|rejected|activated|updated|commented
                $table->text('comment')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // ── workflows.entity_type enum: add 'jsu' (MySQL only) ───────────────
        if (DB::getDriverName() === 'mysql' && Schema::hasTable('workflows')) {
            DB::statement("ALTER TABLE workflows MODIFY entity_type ENUM('course','programme','jsu') NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jsu_blueprints');
        Schema::dropIfExists('jsu_logs');
        Schema::dropIfExists('jsu');

        if (DB::getDriverName() === 'mysql' && Schema::hasTable('workflows')) {
            // Remove rows first to allow narrowing the enum back
            DB::statement("DELETE FROM workflows WHERE entity_type = 'jsu'");
            DB::statement("ALTER TABLE workflows MODIFY entity_type ENUM('course','programme') NOT NULL");
        }
    }
};
