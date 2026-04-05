<?php

namespace App\Services;

use App\Models\Store;
use App\Models\StoreContact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StoreService
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Store::with(['area', 'contacts']);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['rank'])) {
            $query->where('rank', $filters['rank']);
        }

        if (! empty($filters['area_id'])) {
            $query->where('area_id', $filters['area_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Store
    {
        $contacts = $data['contacts'] ?? [];
        unset($data['contacts']);

        $store = Store::create($data);

        foreach ($contacts as $contact) {
            $store->contacts()->create($contact);
        }

        return $store->load('contacts');
    }

    public function update(Store $store, array $data): Store
    {
        $contacts = $data['contacts'] ?? null;
        unset($data['contacts']);

        $store->update($data);

        if ($contacts !== null) {
            $store->contacts()->delete();
            foreach ($contacts as $contact) {
                $store->contacts()->create($contact);
            }
        }

        return $store->load('contacts');
    }

    public function delete(Store $store): void
    {
        $store->delete();
    }

    public function toggleActive(Store $store): Store
    {
        $store->update(['is_active' => ! $store->is_active]);

        return $store;
    }

    public function assignProducts(Store $store, array $productIds): void
    {
        $store->products()->syncWithoutDetaching(
            collect($productIds)->mapWithKeys(fn ($id) => [$id => ['is_active' => true]])->all()
        );
    }

    public function removeProducts(Store $store, array $productIds): void
    {
        $store->products()->detach($productIds);
    }
}
