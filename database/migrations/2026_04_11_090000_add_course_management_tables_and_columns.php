<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            if (! Schema::hasColumn('courses', 'lecturer_id')) {
                $table->foreignId('lecturer_id')->nullable()->constrained('users')->nullOnDelete()->after('programme_id');
            }
            if (! Schema::hasColumn('courses', 'resource_person_id')) {
                $table->foreignId('resource_person_id')->nullable()->constrained('users')->nullOnDelete()->after('lecturer_id');
            }
            if (! Schema::hasColumn('courses', 'vetter_id')) {
                $table->foreignId('vetter_id')->nullable()->constrained('users')->nullOnDelete()->after('resource_person_id');
            }
            if (! Schema::hasColumn('courses', 'status')) {
                $table->string('status', 30)->default('draft')->after('is_active');
            }
            if (! Schema::hasColumn('courses', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('status');
            }
        });

        Schema::create('course_clos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedTinyInteger('clo_no');
            $table->text('statement');
            $table->string('bloom_level', 30);
            $table->timestamps();
        });

        Schema::create('course_requisites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('type', 20)->default('prerequisite');
            $table->string('course_code', 30);
            $table->string('course_name')->nullable();
            $table->timestamps();
        });

        Schema::create('course_assessments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('component', 100);
            $table->decimal('weightage', 5, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('course_topics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedSmallInteger('week_no');
            $table->string('title');
            $table->text('learning_activity')->nullable();
            $table->timestamps();
        });

        Schema::create('course_slt', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('activity', 120);
            $table->decimal('f2f_hours', 6, 2)->default(0);
            $table->decimal('non_f2f_hours', 6, 2)->default(0);
            $table->decimal('independent_hours', 6, 2)->default(0);
            $table->decimal('total_hours', 6, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_slt');
        Schema::dropIfExists('course_topics');
        Schema::dropIfExists('course_assessments');
        Schema::dropIfExists('course_requisites');
        Schema::dropIfExists('course_clos');

        Schema::table('courses', function (Blueprint $table): void {
            foreach (['lecturer_id', 'resource_person_id', 'vetter_id'] as $column) {
                if (Schema::hasColumn('courses', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
            if (Schema::hasColumn('courses', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }
            if (Schema::hasColumn('courses', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
