<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SalesAreaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesAreaController extends Controller
{
    public function __construct(protected SalesAreaService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'active_only', 'manager_id']);
        return response()->json(['data' => $this->service->list($filters)]);
    }

    public function hierarchy(): JsonResponse
    {
        return response()->json(['data' => $this->service->getHierarchy()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:sales_areas,id',
            'manager_id' => 'nullable|exists:users,id',
            'geometry' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $area = $this->service->create($data);
        return response()->json(['data' => $area], 201);
    }

    public function show(int $id): JsonResponse
    {
        $area = \App\Models\SalesArea::with(['manager', 'parent', 'children', 'assignments.user', 'stores'])
            ->findOrFail($id);
        return response()->json(['data' => $area]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $area = \App\Models\SalesArea::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:120',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:sales_areas,id',
            'manager_id' => 'nullable|exists:users,id',
            'geometry' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $area = $this->service->update($area, $data);
        return response()->json(['data' => $area]);
    }

    public function destroy(int $id): JsonResponse
    {
        $area = \App\Models\SalesArea::findOrFail($id);
        $this->service->delete($area);
        return response()->json(null, 204);
    }

    public function assignStores(Request $request, int $id): JsonResponse
    {
        $area = \App\Models\SalesArea::findOrFail($id);
        $request->validate(['store_ids' => 'required|array', 'store_ids.*' => 'exists:stores,id']);
        $this->service->assignStores($area, $request->input('store_ids'));
        return response()->json(['message' => 'Stores assigned']);
    }

    // ─── Assignments ───────────────────────────────────────

    public function addAssignment(Request $request, int $areaId): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'sometimes|in:manager,team_leader,representative',
            'product_lines' => 'nullable|array',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        $data['sales_area_id'] = $areaId;
        $assignment = $this->service->addAssignment($data);
        return response()->json(['data' => $assignment], 201);
    }

    public function removeAssignment(int $areaId, int $assignmentId): JsonResponse
    {
        $assignment = \App\Models\SalesAreaAssignment::where('sales_area_id', $areaId)
            ->findOrFail($assignmentId);
        $this->service->removeAssignment($assignment);
        return response()->json(null, 204);
    }

    public function myAreas(): JsonResponse
    {
        $areas = $this->service->getUserAreas(auth()->id());
        return response()->json(['data' => $areas]);
    }

    public function myStores(): JsonResponse
    {
        $storeIds = $this->service->getUserStores(auth()->id());
        return response()->json(['data' => ['store_ids' => $storeIds, 'count' => count($storeIds)]]);
    }
}
