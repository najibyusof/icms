<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The original academic_core migration created workflowable_type and
 * workflowable_id using morphs() (NOT NULL). The new workflow_instances
 * schema no longer relies on these columns, but they still exist on
 * existing MySQL databases. Make them nullable so insertions that only
 * populate the new entity_type / entity_id columns do not fail.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('workflow_instances', 'workflowable_type')) {
            Schema::table('workflow_instances', function (Blueprint $table): void {
                $table->string('workflowable_type')->nullable()->default(null)->change();
            });
        }

        if (Schema::hasColumn('workflow_instances', 'workflowable_id')) {
            Schema::table('workflow_instances', function (Blueprint $table): void {
                $table->unsignedBigInteger('workflowable_id')->nullable()->default(null)->change();
            });
        }

        if (Schema::hasColumn('workflow_instances', 'initiated_by')) {
            Schema::table('workflow_instances', function (Blueprint $table): void {
                $table->unsignedBigInteger('initiated_by')->nullable()->default(null)->change();
            });
        }
    }

    public function down(): void
    {
        // Intentionally left blank — we do not re-add NOT NULL to these
        // legacy columns as doing so could break existing rows.
    }
};
