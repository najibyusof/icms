<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create group_users pivot table for many-to-many relationship
        Schema::create('group_users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')
                ->constrained('academic_groups')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('role', 50)->default('member')->comment('member, assistant, coordinator');
            $table->timestamps();

            // Prevent duplicate assignments
            $table->unique(['group_id', 'user_id']);
            $table->index(['group_id']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_users');
    }
};
