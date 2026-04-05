<?php

namespace App\Services;

use App\Models\SalesArea;
use App\Models\SalesAreaAssignment;

class SalesAreaService
{
    public function list(array $filters = []): mixed
    {
        $query = SalesArea::with(['manager', 'parent']);

        if (!empty($filters['active_only'])) {
            $query->where('is_active', true);
        }
        if (!empty($filters['manager_id'])) {
            $query->where('manager_id', $filters['manager_id']);
        }
        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->orderBy('name')->get();
    }

    public function getHierarchy(): mixed
    {
        return SalesArea::with(['children.children', 'manager', 'assignments.user'])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): SalesArea
    {
        $data['tenant_id'] = app('current_tenant_id');
        return SalesArea::create($data);
    }

    public function update(SalesArea $area, array $data): SalesArea
    {
        $area->update($data);
        return $area->fresh();
    }

    public function delete(SalesArea $area): void
    {
        $area->delete();
    }

    public function assignStores(SalesArea $area, array $storeIds): void
    {
        $area->stores()->sync($storeIds);
    }

    public function addAssignment(array $data): SalesAreaAssignment
    {
        $data['tenant_id'] = app('current_tenant_id');
        return SalesAreaAssignment::create($data);
    }

    public function updateAssignment(SalesAreaAssignment $assignment, array $data): SalesAreaAssignment
    {
        $assignment->update($data);
        return $assignment->fresh();
    }

    public function removeAssignment(SalesAreaAssignment $assignment): void
    {
        $assignment->delete();
    }

    /**
     * Get all assignments for a user, optionally with their cascaded areas.
     */
    public function getUserAreas(int $userId): mixed
    {
        return SalesAreaAssignment::with(['salesArea.stores', 'salesArea.children'])
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', now());
            })
            ->get();
    }

    /**
     * Get stores accessible to a user via their sales area assignments.
     */
    public function getUserStores(int $userId): array
    {
        $assignments = $this->getUserAreas($userId);
        $storeIds = [];

        foreach ($assignments as $assignment) {
            $area = $assignment->salesArea;
            $storeIds = array_merge($storeIds, $area->stores->pluck('id')->toArray());

            // Cascade to child areas
            foreach ($area->children as $child) {
                $childArea = SalesArea::with('stores')->find($child->id);
                if ($childArea) {
                    $storeIds = array_merge($storeIds, $childArea->stores->pluck('id')->toArray());
                }
            }
        }

        return array_unique($storeIds);
    }
}
