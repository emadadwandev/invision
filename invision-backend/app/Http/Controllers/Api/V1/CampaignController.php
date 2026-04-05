<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\CreateCampaignEntryRequest;
use App\Http\Requests\Campaign\CreateCampaignRequest;
use App\Http\Requests\Campaign\CreateCampaignTaskRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use App\Http\Resources\CampaignEntryResource;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\CampaignTaskPhotoResource;
use App\Http\Resources\CampaignTaskResource;
use App\Http\Resources\PosmMaterialResource;
use App\Http\Resources\PosmPlacementResource;
use App\Models\Campaign;
use App\Models\CampaignTask;
use App\Models\PosmMaterial;
use App\Models\PosmPlacement;
use App\Services\CampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignController extends Controller
{
    public function __construct(
        private readonly CampaignService $campaignService,
    ) {}

    // ─── Campaigns ────────────────────────────────────────────

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Campaign::class);

        $campaigns = $this->campaignService->listCampaigns(
            $request->only(['search', 'status', 'type']),
            $request->integer('per_page', 15)
        );

        return CampaignResource::collection($campaigns);
    }

    public function store(CreateCampaignRequest $request): JsonResponse
    {
        $this->authorize('create', Campaign::class);

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['tenant_id'] = $request->user()->tenant_id;

        $campaign = $this->campaignService->createCampaign($data);

        return (new CampaignResource($campaign))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Campaign $campaign): CampaignResource
    {
        $this->authorize('view', $campaign);

        $campaign->load(['creator', 'stores', 'products', 'tasks.assignedUser', 'tasks.store']);

        return new CampaignResource($campaign);
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): CampaignResource
    {
        $this->authorize('update', $campaign);

        $campaign = $this->campaignService->updateCampaign($campaign, $request->validated());

        return new CampaignResource($campaign);
    }

    public function destroy(Campaign $campaign): JsonResponse
    {
        $this->authorize('delete', $campaign);

        $this->campaignService->deleteCampaign($campaign);

        return response()->json(null, 204);
    }

    // ─── Campaign Tasks ───────────────────────────────────────

    public function tasks(Request $request): AnonymousResourceCollection
    {
        $tasks = $this->campaignService->listTasks(
            $request->only(['campaign_id', 'assigned_to', 'status', 'store_id']),
            $request->integer('per_page', 15)
        );

        return CampaignTaskResource::collection($tasks);
    }

    public function storeTask(CreateCampaignTaskRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $task = $this->campaignService->createTask($data);

        return (new CampaignTaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function showTask(CampaignTask $campaignTask): CampaignTaskResource
    {
        $campaignTask->load(['campaign', 'store', 'assignedUser', 'verifier', 'photos']);

        return new CampaignTaskResource($campaignTask);
    }

    public function completeTask(Request $request, CampaignTask $campaignTask): CampaignTaskResource
    {
        $task = $this->campaignService->completeTask(
            $campaignTask,
            $request->input('notes')
        );

        return new CampaignTaskResource($task->load(['campaign', 'store', 'photos']));
    }

    public function verifyTask(CampaignTask $campaignTask): CampaignTaskResource
    {
        $task = $this->campaignService->verifyTask($campaignTask, auth()->id());

        return new CampaignTaskResource($task->load(['campaign', 'store', 'verifier']));
    }

    public function rejectTask(Request $request, CampaignTask $campaignTask): CampaignTaskResource
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $task = $this->campaignService->rejectTask(
            $campaignTask,
            auth()->id(),
            $request->input('reason')
        );

        return new CampaignTaskResource($task->load(['campaign', 'store', 'verifier']));
    }

    public function uploadTaskPhoto(Request $request, CampaignTask $campaignTask): JsonResponse
    {
        $request->validate([
            'photo_path' => ['required', 'string', 'max:500'],
            'caption' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:proof,before,after'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $photo = $this->campaignService->addTaskPhoto($campaignTask, $request->all());

        return (new CampaignTaskPhotoResource($photo))
            ->response()
            ->setStatusCode(201);
    }

    // ─── Campaign Entries ─────────────────────────────────────

    public function entries(Request $request): AnonymousResourceCollection
    {
        $entries = $this->campaignService->listEntries(
            $request->only(['campaign_id', 'store_id', 'user_id']),
            $request->integer('per_page', 15)
        );

        return CampaignEntryResource::collection($entries);
    }

    public function storeEntry(CreateCampaignEntryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['tenant_id'] = $request->user()->tenant_id;

        $entry = $this->campaignService->createEntry($data);

        return (new CampaignEntryResource($entry))
            ->response()
            ->setStatusCode(201);
    }

    // ─── POSM Materials ───────────────────────────────────────

    public function materials(Request $request): AnonymousResourceCollection
    {
        $materials = $this->campaignService->listMaterials(
            $request->only(['search', 'is_active']),
            $request->integer('per_page', 15)
        );

        return PosmMaterialResource::collection($materials);
    }

    public function storeMaterial(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['nullable', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:100'],
            'quantity_available' => ['nullable', 'integer', 'min:0'],
            'image_path' => ['nullable', 'string', 'max:500'],
        ]);

        $data = $request->all();
        $data['tenant_id'] = $request->user()->tenant_id;

        $material = $this->campaignService->createMaterial($data);

        return (new PosmMaterialResource($material))
            ->response()
            ->setStatusCode(201);
    }

    public function showMaterial(PosmMaterial $posmMaterial): PosmMaterialResource
    {
        $posmMaterial->load('placements.store');

        return new PosmMaterialResource($posmMaterial);
    }

    public function updateMaterial(Request $request, PosmMaterial $posmMaterial): PosmMaterialResource
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['nullable', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:100'],
            'quantity_available' => ['nullable', 'integer', 'min:0'],
            'image_path' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $material = $this->campaignService->updateMaterial($posmMaterial, $request->all());

        return new PosmMaterialResource($material);
    }

    public function destroyMaterial(PosmMaterial $posmMaterial): JsonResponse
    {
        $this->campaignService->deleteMaterial($posmMaterial);

        return response()->json(null, 204);
    }

    // ─── POSM Placements ─────────────────────────────────────

    public function placements(Request $request): AnonymousResourceCollection
    {
        $placements = $this->campaignService->listPlacements(
            $request->only(['store_id', 'posm_material_id', 'condition']),
            $request->integer('per_page', 15)
        );

        return PosmPlacementResource::collection($placements);
    }

    public function storePlacement(Request $request): JsonResponse
    {
        $request->validate([
            'posm_material_id' => ['required', 'exists:posm_materials,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'placed_at' => ['required', 'date'],
            'photo_path' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data = $request->all();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['placed_by'] = $request->user()->id;

        $placement = $this->campaignService->createPlacement($data);

        return (new PosmPlacementResource($placement))
            ->response()
            ->setStatusCode(201);
    }

    public function checkPlacement(Request $request, PosmPlacement $posmPlacement): JsonResponse
    {
        $request->validate([
            'condition' => ['required', 'string', 'in:good,damaged,missing,needs_replacement'],
            'photo_path' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'replacement_requested' => ['nullable', 'boolean'],
        ]);

        $data = $request->all();
        $data['checked_by'] = auth()->id();

        $log = $this->campaignService->logPosmCheck($posmPlacement, $data);

        return response()->json([
            'data' => $log->load('placement.material'),
            'message' => 'POSM check logged successfully.',
        ]);
    }

    // ─── My Tasks (Mobile) ───────────────────────────────────

    public function myTasks(Request $request): AnonymousResourceCollection
    {
        $tasks = $this->campaignService->myTasks(
            $request->user()->id,
            $request->input('status')
        );

        return CampaignTaskResource::collection($tasks);
    }
}
