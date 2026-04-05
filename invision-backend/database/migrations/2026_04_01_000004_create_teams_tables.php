<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('parent_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('parent_team_id');
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('position')->default('member');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('team_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('to_team_id')->constrained('teams')->cascadeOnDelete();
            $table->timestamp('transferred_at')->useCurrent();
            $table->text('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('user_id');
            $table->index('transferred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_transfers');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
    }
};
