<?php

use App\Models\Store;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_plans', function (Blueprint $table) {
            // ISO weekdays (1=Mon..7=Sun) the rep works; used for stores with no fixed day
            $table->json('work_days')->nullable()->after('frequency');
        });

        Schema::table('route_plan_stores', function (Blueprint $table) {
            $table->unsignedTinyInteger('day_of_week')->nullable()->after('store_id'); // ISO 1..7, null = every work day
            $table->unsignedTinyInteger('week_number')->nullable()->after('day_of_week'); // cycle week, null = every week
            $table->date('week_start')->nullable()->after('week_number'); // set = override row for that specific week only
            // Also serves the route_plan_id FK once the unique below is gone (MySQL)
            $table->index(['route_plan_id', 'week_start']);
        });

        Schema::table('route_plan_stores', function (Blueprint $table) {
            // A store may now appear on several days/weeks of the same plan
            $table->dropUnique(['route_plan_id', 'store_id']);
        });

        // Every store needs a scannable QR code for visit check-in
        DB::table('stores')->whereNull('qr_code')->orderBy('id')->each(function ($store) {
            DB::table('stores')->where('id', $store->id)->update(['qr_code' => Store::generateQrCode()]);
        });
    }

    public function down(): void
    {
        Schema::table('route_plan_stores', function (Blueprint $table) {
            $table->unique(['route_plan_id', 'store_id']);
        });

        Schema::table('route_plan_stores', function (Blueprint $table) {
            $table->dropIndex(['route_plan_id', 'week_start']);
            $table->dropColumn(['day_of_week', 'week_number', 'week_start']);
        });

        Schema::table('route_plans', function (Blueprint $table) {
            $table->dropColumn('work_days');
        });
    }
};
