<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Comprehensive audit trail
        if (Schema::hasTable('audit_logs')) {
            Schema::drop('audit_logs');
        }
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 100)->nullable();
            $table->string('action', 30); // create, update, delete, login, logout, etc.
            $table->string('entity_type', 60); // model class or resource name
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable(); // previous state
            $table->json('new_values')->nullable(); // new state
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('method', 10)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'entity_type', 'entity_id']);
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'action']);
            $table->index('created_at');
        });

        // MFA settings per user (add missing columns only)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'mfa_enabled')) {
                $table->boolean('mfa_enabled')->default(false)->after('mfa_secret');
            }
            if (!Schema::hasColumn('users', 'mfa_recovery_codes')) {
                $table->json('mfa_recovery_codes')->nullable()->after('mfa_secret');
            }
            if (!Schema::hasColumn('users', 'mfa_confirmed_at')) {
                $table->timestamp('mfa_confirmed_at')->nullable()->after('mfa_recovery_codes');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mfa_enabled')) {
                $table->dropColumn('mfa_enabled');
            }
            if (Schema::hasColumn('users', 'mfa_recovery_codes')) {
                $table->dropColumn('mfa_recovery_codes');
            }
            if (Schema::hasColumn('users', 'mfa_confirmed_at')) {
                $table->dropColumn('mfa_confirmed_at');
            }
        });
    }
};
