<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('report_templates')) {
            Schema::create('report_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->string('name');
                $table->string('type')->default('custom'); // overview, sales, field_activity, custom
                $table->text('description')->nullable();
                $table->json('config'); // entity, columns, filters, group_by, order, etc.
                $table->json('layout')->nullable(); // presentation layout hints
                $table->boolean('is_shared')->default(false);
                $table->boolean('is_favorite')->default(false);
                $table->string('schedule')->nullable(); // cron expression for auto-generation
                $table->timestamp('last_generated_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('tenant_id');
                $table->index('created_by');
                $table->index(['tenant_id', 'type']);
            });
        }

        if (!Schema::hasTable('saved_exports')) {
            Schema::create('saved_exports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('report_template_id')->nullable();
                $table->string('title');
                $table->string('format'); // csv, excel, pdf, presentation, html
                $table->string('file_path');
                $table->unsignedBigInteger('file_size')->default(0);
                $table->json('parameters')->nullable();
                $table->timestamps();

                $table->index('tenant_id');
                $table->index('user_id');
                $table->index('report_template_id');
            });
        }

        if (!Schema::hasTable('presentation_templates')) {
            Schema::create('presentation_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('name');
                $table->string('category')->default('general'); // general, market_review, sales_review, field_report
                $table->text('description')->nullable();
                $table->json('slide_definitions'); // array of slide configs
                $table->json('theme')->nullable(); // colors, fonts, logo
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('presentation_templates');
        Schema::dropIfExists('saved_exports');
        Schema::dropIfExists('report_templates');
    }
};
