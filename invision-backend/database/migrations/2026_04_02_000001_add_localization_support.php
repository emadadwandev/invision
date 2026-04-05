<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add preferred locale to users
        if (!Schema::hasColumn('users', 'locale')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('locale', 10)->default('en')->after('role');
            });
        }

        // Translation overrides per tenant (custom label overrides)
        if (!Schema::hasTable('translation_overrides')) {
            Schema::create('translation_overrides', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('locale', 10);
                $table->string('group', 60); // e.g. 'messages', 'labels'
                $table->string('key', 120);
                $table->text('value');
                $table->timestamps();

                $table->unique(['tenant_id', 'locale', 'group', 'key'], 'translation_unique');
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_overrides');

        if (Schema::hasColumn('users', 'locale')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('locale');
            });
        }
    }
};
