<?php

namespace App\Services;

use App\Models\Competitor;
use App\Models\CompetitorObservation;
use App\Models\CompetitorProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CompetitorService
{
    // ─── Competitors ───────────────────────────────────────────

    public function listCompetitors(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Competitor::query()->withCount(['products', 'observations']);

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function createCompetitor(array $data): Competitor
    {
        return Competitor::create($data);
    }

    public function updateCompetitor(Competitor $competitor, array $data): Competitor
    {
        $competitor->update($data);

        return $competitor->fresh();
    }

    public function deleteCompetitor(Competitor $competitor): void
    {
        $competitor->delete();
    }

    // ─── Competitor Products ───────────────────────────────────

    public function listProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CompetitorProduct::query()->with('competitor');

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('sku', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('barcode', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (! empty($filters['competitor_id'])) {
            $query->where('competitor_id', $filters['competitor_id']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function createProduct(array $data): CompetitorProduct
    {
        return CompetitorProduct::create($data);
    }

    public function updateProduct(CompetitorProduct $product, array $data): CompetitorProduct
    {
        $product->update($data);

        return $product->fresh(['competitor']);
    }

    public function deleteProduct(CompetitorProduct $product): void
    {
        $product->delete();
    }

    // ─── Observations ──────────────────────────────────────────

    public function listObservations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CompetitorObservation::query()
            ->with(['store', 'user', 'competitor', 'competitorProduct']);

        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (! empty($filters['competitor_id'])) {
            $query->where('competitor_id', $filters['competitor_id']);
        }

        if (! empty($filters['observation_type'])) {
            $query->where('observation_type', $filters['observation_type']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['store_visit_id'])) {
            $query->where('store_visit_id', $filters['store_visit_id']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('observed_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('observed_at', '<=', $filters['to']);
        }

        return $query->orderByDesc('observed_at')->paginate($perPage);
    }

    public function createObservation(array $data): CompetitorObservation
    {
        return CompetitorObservation::create($data);
    }

    public function updateObservation(CompetitorObservation $observation, array $data): CompetitorObservation
    {
        $observation->update($data);

        return $observation->fresh(['store', 'user', 'competitor', 'competitorProduct']);
    }

    public function deleteObservation(CompetitorObservation $observation): void
    {
        $observation->delete();
    }

    // ─── Visit Observations ────────────────────────────────────

    public function getVisitObservations(int $storeVisitId): \Illuminate\Database\Eloquent\Collection
    {
        return CompetitorObservation::query()
            ->where('store_visit_id', $storeVisitId)
            ->with(['competitor', 'competitorProduct'])
            ->orderByDesc('observed_at')
            ->get();
    }

    // ─── Analysis ──────────────────────────────────────────────

    public function competitorAnalysis(array $filters = []): array
    {
        $query = CompetitorObservation::query()
            ->selectRaw('competitor_id, observation_type, COUNT(*) as total_observations, AVG(price) as avg_price, SUM(quantity) as total_quantity')
            ->groupBy('competitor_id', 'observation_type');

        if (! empty($filters['from'])) {
            $query->whereDate('observed_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('observed_at', '<=', $filters['to']);
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        $results = $query->get();

        $grouped = [];
        foreach ($results as $row) {
            $competitorId = $row->competitor_id;
            if (! isset($grouped[$competitorId])) {
                $competitor = Competitor::find($competitorId);
                $grouped[$competitorId] = [
                    'competitor' => $competitor ? $competitor->name : 'Unknown',
                    'types' => [],
                    'total_observations' => 0,
                ];
            }
            $grouped[$competitorId]['types'][] = [
                'type' => $row->observation_type,
                'count' => $row->total_observations,
                'avg_price' => round($row->avg_price, 2),
                'total_quantity' => $row->total_quantity,
            ];
            $grouped[$competitorId]['total_observations'] += $row->total_observations;
        }

        return array_values($grouped);
    }
}
