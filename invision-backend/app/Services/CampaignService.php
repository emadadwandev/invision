<?php

namespace App\Services;

use App\Enums\CampaignStatus;
use App\Enums\PosmCondition;
use App\Enums\TaskStatus;
use App\Models\Campaign;
use App\Models\CampaignEntry;
use App\Models\CampaignTask;
use App\Models\CampaignTaskPhoto;
use App\Models\PosmCheckLog;
use App\Models\PosmMaterial;
use App\Models\PosmPlacement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CampaignService
{
    // ─── Campaigns ────────────────────────────────────────────

    public function listCampaigns(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Campaign::with(['creator', 'stores', 'products']);

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function createCampaign(array $data): Campaign
    {
        $stores = $data['store_ids'] ?? [];
        $products = $data['product_ids'] ?? [];
        unset($data['store_ids'], $data['product_ids']);

        $campaign = Campaign::create($data);

        if (! empty($stores)) {
            $campaign->stores()->attach($stores);
        }

        if (! empty($products)) {
            $campaign->products()->attach($products);
        }

        return $campaign->load(['creator', 'stores', 'products']);
    }

    public function updateCampaign(Campaign $campaign, array $data): Campaign
    {
        $stores = $data['store_ids'] ?? null;
        $products = $data['product_ids'] ?? null;
        unset($data['store_ids'], $data['product_ids']);

        $campaign->update($data);

        if ($stores !== null) {
            $campaign->stores()->sync($stores);
        }

        if ($products !== null) {
            $campaign->products()->sync($products);
        }

        return $campaign->load(['creator', 'stores', 'products']);
    }

    public function deleteCampaign(Campaign $campaign): void
    {
        $campaign->delete();
    }

    // ─── Campaign Tasks ───────────────────────────────────────

    public function listTasks(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CampaignTask::with(['campaign', 'store', 'assignedUser', 'photos']);

        if (! empty($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function createTask(array $data): CampaignTask
    {
        return CampaignTask::create($data)->load(['campaign', 'store', 'assignedUser']);
    }

    public function completeTask(CampaignTask $task, ?string $notes = null): CampaignTask
    {
        $task->update([
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
            'notes' => $notes ?? $task->notes,
        ]);

        return $task;
    }

    public function verifyTask(CampaignTask $task, int $verifierId): CampaignTask
    {
        $task->update([
            'status' => TaskStatus::Verified,
            'verified_by' => $verifierId,
            'verified_at' => now(),
        ]);

        return $task;
    }

    public function rejectTask(CampaignTask $task, int $verifierId, string $reason): CampaignTask
    {
        $task->update([
            'status' => TaskStatus::Rejected,
            'verified_by' => $verifierId,
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $task;
    }

    public function addTaskPhoto(CampaignTask $task, array $data): CampaignTaskPhoto
    {
        return $task->photos()->create($data);
    }

    // ─── Campaign Entries ─────────────────────────────────────

    public function createEntry(array $data): CampaignEntry
    {
        return CampaignEntry::create($data)->load(['campaign', 'store', 'user']);
    }

    public function listEntries(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CampaignEntry::with(['campaign', 'store', 'user']);

        if (! empty($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    // ─── POSM Materials ───────────────────────────────────────

    public function listMaterials(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PosmMaterial::query();

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function createMaterial(array $data): PosmMaterial
    {
        return PosmMaterial::create($data);
    }

    public function updateMaterial(PosmMaterial $material, array $data): PosmMaterial
    {
        $material->update($data);

        return $material;
    }

    public function deleteMaterial(PosmMaterial $material): void
    {
        $material->delete();
    }

    // ─── POSM Placements ─────────────────────────────────────

    public function listPlacements(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PosmPlacement::with(['material', 'store', 'placedByUser']);

        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (! empty($filters['posm_material_id'])) {
            $query->where('posm_material_id', $filters['posm_material_id']);
        }

        if (! empty($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function createPlacement(array $data): PosmPlacement
    {
        return PosmPlacement::create($data)->load(['material', 'store']);
    }

    public function logPosmCheck(PosmPlacement $placement, array $data): PosmCheckLog
    {
        $log = $placement->checkLogs()->create($data);

        $placement->update([
            'condition' => $data['condition'],
            'last_checked_at' => now(),
        ]);

        if (($data['replacement_requested'] ?? false)) {
            $placement->update(['condition' => PosmCondition::NeedsReplacement]);
        }

        return $log;
    }

    // ─── My Tasks (Mobile) ───────────────────────────────────

    public function myTasks(int $userId, ?string $status = null): LengthAwarePaginator
    {
        $query = CampaignTask::with(['campaign', 'store', 'photos'])
            ->where('assigned_to', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate(15);
    }
}
