<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('workflows')) {
            Schema::create('workflows', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->enum('entity_type', ['course', 'programme', 'jsu'])->index();
                $table->boolean('is_active')->default(true);
                $table->json('config')->nullable()->comment('Dynamic workflow configuration');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['entity_type', 'is_active']);
            });
        }

        if (!Schema::hasTable('workflow_steps')) {
            Schema::create('workflow_steps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
                $table->integer('step_number');
                $table->string('title');
                $table->text('description')->nullable();
                $table->json('roles_required');
                $table->integer('approval_level')->default(1)->comment('1=department, 2=college, 3=faculty');
                $table->enum('action_type', ['approve', 'review', 'clarification'])->default('approve');
                $table->boolean('allow_rejection')->default(true);
                $table->boolean('requires_comment')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['workflow_id', 'step_number']);
                $table->index(['workflow_id', 'approval_level']);
            });
        }

        if (!Schema::hasTable('workflow_instances')) {
            Schema::create('workflow_instances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
                $table->morphs('entity');
                $table->foreignId('current_step_id')->nullable()->constrained('workflow_steps');
                $table->enum('status', ['draft', 'in_progress', 'approved', 'rejected', 'withdrawn'])->default('draft');
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('submitted_by')->nullable()->constrained('users');
                $table->timestamp('submitted_at')->nullable();
                $table->foreignId('final_approved_by')->nullable()->constrained('users');
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('rejected_by')->nullable()->constrained('users');
                $table->timestamp('rejected_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['workflow_id', 'status']);
                $table->index(['entity_type', 'entity_id']);
                $table->index(['created_by', 'status']);
            });
        } else {
            Schema::table('workflow_instances', function (Blueprint $table) {
                if (!Schema::hasColumn('workflow_instances', 'workflow_id')) {
                    $table->unsignedBigInteger('workflow_id')->nullable()->after('id');
                    $table->index(['workflow_id', 'status']);
                }

                if (!Schema::hasColumn('workflow_instances', 'entity_type')) {
                    $table->string('entity_type')->nullable()->after('workflow_id');
                }

                if (!Schema::hasColumn('workflow_instances', 'entity_id')) {
                    $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
                }

                if (!Schema::hasColumn('workflow_instances', 'current_step_id')) {
                    $table->unsignedBigInteger('current_step_id')->nullable()->after('entity_id');
                }

                if (!Schema::hasColumn('workflow_instances', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('status');
                    $table->index(['created_by', 'status']);
                }

                if (!Schema::hasColumn('workflow_instances', 'submitted_by')) {
                    $table->unsignedBigInteger('submitted_by')->nullable()->after('created_by');
                }

                if (!Schema::hasColumn('workflow_instances', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable()->after('submitted_by');
                }

                if (!Schema::hasColumn('workflow_instances', 'final_approved_by')) {
                    $table->unsignedBigInteger('final_approved_by')->nullable()->after('submitted_at');
                }

                if (!Schema::hasColumn('workflow_instances', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('final_approved_by');
                }

                if (!Schema::hasColumn('workflow_instances', 'rejected_by')) {
                    $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
                }

                if (!Schema::hasColumn('workflow_instances', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                }

                if (!Schema::hasColumn('workflow_instances', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('rejected_at');
                }

                if (!Schema::hasColumn('workflow_instances', 'metadata')) {
                    $table->json('metadata')->nullable()->after('rejection_reason');
                }

                if (!Schema::hasColumn('workflow_instances', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            if (Schema::hasColumn('workflow_instances', 'workflowable_type')) {
                DB::table('workflow_instances')
                    ->whereNull('entity_type')
                    ->update([
                        'entity_type' => DB::raw('workflowable_type'),
                        'entity_id' => DB::raw('workflowable_id'),
                    ]);
            }

            if (Schema::hasColumn('workflow_instances', 'initiated_by')) {
                DB::table('workflow_instances')
                    ->whereNull('created_by')
                    ->update([
                        'created_by' => DB::raw('initiated_by'),
                    ]);
            }

            if (Schema::hasColumn('workflow_instances', 'current_stage')) {
                $firstWorkflowId = DB::table('workflows')->value('id');

                if ($firstWorkflowId) {
                    DB::table('workflow_instances')
                        ->whereNull('workflow_id')
                        ->update(['workflow_id' => $firstWorkflowId]);
                }
            }

            if (Schema::hasColumn('workflow_instances', 'entity_type') && Schema::hasColumn('workflow_instances', 'entity_id')) {
                Schema::table('workflow_instances', function (Blueprint $table) {
                    $table->index(['entity_type', 'entity_id']);
                });
            }
        }

        if (!Schema::hasTable('workflow_logs')) {
            Schema::create('workflow_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();
                $table->foreignId('workflow_step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users');
                $table->enum('action', ['submitted', 'approved', 'rejected', 'commented', 'clarification_requested', 'returned', 'withdrawn'])->index();
                $table->text('comment')->nullable();
                $table->json('data')->nullable()->comment('Additional context data');
                $table->ipAddress('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();

                $table->index(['workflow_instance_id', 'action']);
                $table->index(['user_id', 'created_at']);

                if (in_array(DB::getDriverName(), ['mysql', 'pgsql'], true)) {
                    $table->fullText(['comment']);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workflow_logs')) {
            Schema::dropIfExists('workflow_logs');
        }
        if (Schema::hasTable('workflow_instances')) {
            Schema::dropIfExists('workflow_instances');
        }
        if (Schema::hasTable('workflow_steps')) {
            Schema::dropIfExists('workflow_steps');
        }
        if (Schema::hasTable('workflows')) {
            Schema::dropIfExists('workflows');
        }
    }
};
