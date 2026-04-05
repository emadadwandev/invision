<?php

namespace Database\Seeders;

use App\Enums\CampaignStatus;
use App\Enums\CampaignType;
use App\Enums\PosmCondition;
use App\Enums\TaskStatus;
use App\Models\Campaign;
use App\Models\CampaignEntry;
use App\Models\CampaignTask;
use App\Models\PosmMaterial;
use App\Models\PosmPlacement;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'invision-default')->first();

        if (! $tenant) {
            return;
        }

        $stores = Store::where('tenant_id', $tenant->id)->where('is_active', true)->get();
        $products = Product::where('tenant_id', $tenant->id)->where('is_active', true)->get();
        $admin = User::where('tenant_id', $tenant->id)->where('role', 'super_admin')->first();
        $fieldUsers = User::where('tenant_id', $tenant->id)
            ->whereIn('role', ['field_force', 'promoter', 'merchandiser', 'sales_representative'])
            ->where('is_active', true)
            ->get();

        if (! $admin || $stores->isEmpty()) {
            return;
        }

        if ($fieldUsers->isEmpty()) {
            $fieldUsers = User::where('tenant_id', $tenant->id)->where('is_active', true)->take(2)->get();
        }

        // --- Campaign 1: Summer Promotion ---
        $campaign1 = Campaign::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Summer Promotion 2026'],
            [
                'description' => 'Summer promotional campaign with discounts on selected products across key stores.',
                'type' => CampaignType::Promotion,
                'status' => CampaignStatus::Active,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addMonths(2),
                'budget' => 5000.00,
                'spent' => 1250.00,
                'offer_details' => ['discount_percent' => 20, 'min_purchase' => 50],
                'reward_details' => ['bonus_points' => 100],
                'created_by' => $admin->id,
            ]
        );

        if ($campaign1->wasRecentlyCreated) {
            $campaign1->stores()->attach($stores->take(3)->pluck('id'));
            if ($products->isNotEmpty()) {
                $campaign1->products()->attach($products->take(4)->pluck('id'));
            }

            // Tasks for campaign 1
            foreach ($stores->take(3) as $store) {
                $assignee = $fieldUsers->random();
                $task = CampaignTask::create([
                    'tenant_id' => $tenant->id,
                    'campaign_id' => $campaign1->id,
                    'store_id' => $store->id,
                    'assigned_to' => $assignee->id,
                    'instructions' => "Set up promotional display and distribute flyers at {$store->name}.",
                    'status' => TaskStatus::Pending,
                ]);
            }

            // One completed task
            if ($stores->count() >= 1 && $fieldUsers->isNotEmpty()) {
                $completedTask = CampaignTask::create([
                    'tenant_id' => $tenant->id,
                    'campaign_id' => $campaign1->id,
                    'store_id' => $stores->first()->id,
                    'assigned_to' => $fieldUsers->first()->id,
                    'instructions' => 'Verify shelf placement and take proof photos.',
                    'status' => TaskStatus::Completed,
                    'completed_at' => now()->subDay(),
                ]);

                $completedTask->photos()->create([
                    'photo_path' => 'task-photos/sample-proof-1.jpg',
                    'caption' => 'Display setup at entrance',
                    'type' => 'proof',
                ]);
            }

            // Sample entry
            CampaignEntry::create([
                'tenant_id' => $tenant->id,
                'campaign_id' => $campaign1->id,
                'campaign_task_id' => null,
                'store_id' => $stores->first()->id,
                'user_id' => $fieldUsers->first()->id,
                'entry_type' => 'qr_scan',
                'code' => 'SUMMER2026-' . strtoupper(substr(md5(rand()), 0, 6)),
                'quantity' => 1,
            ]);
        }

        // --- Campaign 2: Buy 2 Get 1 Free ---
        $campaign2 = Campaign::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Buy 2 Get 1 Free'],
            [
                'description' => 'Buy two products and get one free campaign targeting all stores.',
                'type' => CampaignType::BuyGetFree,
                'status' => CampaignStatus::Scheduled,
                'start_date' => now()->addWeek(),
                'end_date' => now()->addMonths(1),
                'budget' => 3000.00,
                'spent' => 0,
                'offer_details' => ['buy_quantity' => 2, 'free_quantity' => 1],
                'reward_details' => null,
                'created_by' => $admin->id,
            ]
        );

        if ($campaign2->wasRecentlyCreated && $stores->count() >= 2) {
            $campaign2->stores()->attach($stores->take(2)->pluck('id'));
            if ($products->count() >= 2) {
                $campaign2->products()->attach($products->take(2)->pluck('id'));
            }
        }

        // --- Campaign 3: Draft Sampling ---
        Campaign::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Product Sampling Q3'],
            [
                'description' => 'In-store product sampling campaign — draft for planning.',
                'type' => CampaignType::Sampling,
                'status' => CampaignStatus::Draft,
                'start_date' => now()->addMonths(2),
                'end_date' => now()->addMonths(3),
                'budget' => 8000.00,
                'spent' => 0,
                'offer_details' => null,
                'reward_details' => null,
                'created_by' => $admin->id,
            ]
        );

        // --- POSM Materials ---
        $material1 = PosmMaterial::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Floor Standing Banner'],
            [
                'type' => 'banner',
                'sku' => 'POSM-BNR-001',
                'description' => 'Roll-up floor standing banner (180cm x 80cm).',
                'quantity_available' => 50,
                'is_active' => true,
            ]
        );

        $material2 = PosmMaterial::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Shelf Talker'],
            [
                'type' => 'shelf_talker',
                'sku' => 'POSM-SHT-001',
                'description' => 'Shelf talker card for product pricing display.',
                'quantity_available' => 200,
                'is_active' => true,
            ]
        );

        $material3 = PosmMaterial::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Counter Display Unit'],
            [
                'type' => 'display',
                'sku' => 'POSM-CDU-001',
                'description' => 'Countertop cardboard display for impulse products.',
                'quantity_available' => 30,
                'is_active' => true,
            ]
        );

        // --- POSM Placements ---
        if ($stores->count() >= 2 && $fieldUsers->isNotEmpty()) {
            $placement1 = PosmPlacement::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'posm_material_id' => $material1->id, 'store_id' => $stores->first()->id],
                [
                    'condition' => PosmCondition::Good,
                    'placed_by' => $fieldUsers->first()->id,
                    'placed_at' => now()->subWeeks(2),
                    'last_checked_at' => now()->subDays(3),
                ]
            );

            if ($placement1->wasRecentlyCreated) {
                $placement1->checkLogs()->create([
                    'checked_by' => $fieldUsers->first()->id,
                    'condition' => PosmCondition::Good,
                    'notes' => 'Banner in good condition, visible from entrance.',
                    'replacement_requested' => false,
                ]);
            }

            $placement2 = PosmPlacement::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'posm_material_id' => $material2->id, 'store_id' => $stores->skip(1)->first()->id],
                [
                    'condition' => PosmCondition::Damaged,
                    'placed_by' => $fieldUsers->first()->id,
                    'placed_at' => now()->subMonth(),
                    'last_checked_at' => now()->subDay(),
                ]
            );

            if ($placement2->wasRecentlyCreated) {
                $placement2->checkLogs()->create([
                    'checked_by' => $fieldUsers->first()->id,
                    'condition' => PosmCondition::Damaged,
                    'notes' => 'Shelf talker torn on right side. Needs replacement.',
                    'replacement_requested' => true,
                ]);
            }
        }
    }
}
