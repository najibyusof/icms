<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'sso_provider')) {
                $table->string('sso_provider', 50)->nullable()->after('faculty');
            }

            if (!Schema::hasColumn('users', 'sso_subject')) {
                $table->string('sso_subject', 191)->nullable()->after('sso_provider');
                $table->index(['sso_provider', 'sso_subject'], 'users_sso_provider_subject_idx');
            }

            if (!Schema::hasColumn('users', 'last_sso_login_at')) {
                $table->timestamp('last_sso_login_at')->nullable()->after('sso_subject');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'last_sso_login_at')) {
                $table->dropColumn('last_sso_login_at');
            }

            if (Schema::hasColumn('users', 'sso_subject')) {
                $table->dropIndex('users_sso_provider_subject_idx');
                $table->dropColumn('sso_subject');
            }

            if (Schema::hasColumn('users', 'sso_provider')) {
                $table->dropColumn('sso_provider');
            }
        });
    }
};
