<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\CreateCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use App\Models\Campaign;
use App\Models\CampaignTask;
use App\Models\PosmMaterial;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CampaignService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function __construct(
        private readonly CampaignService $campaignService,
    ) {}

    // ─── Campaigns ────────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Campaign::class);

        $campaigns = $this->campaignService->listCampaigns(
            $request->only(['search', 'status', 'type']),
            $request->integer('per_page', 15)
        );

        return view('pages.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        $this->authorize('create', Campaign::class);

        $stores = Store::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $users = User::where('is_active', true)->get();

        return view('pages.campaigns.create', compact('stores', 'products', 'users'));
    }

    public function store(CreateCampaignRequest $request): RedirectResponse
    {
        $this->authorize('create', Campaign::class);

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['tenant_id'] = $request->user()->tenant_id;

        $this->campaignService->createCampaign($data);

        return redirect()->route('campaigns.index')
            ->with('success', 'Campaign created successfully.');
    }

    public function show(Campaign $campaign): View
    {
        $this->authorize('view', $campaign);

        $campaign->load([
            'creator',
            'stores',
            'products',
            'tasks' => fn ($q) => $q->with(['assignedUser', 'store', 'photos'])->latest()->limit(20),
            'entries' => fn ($q) => $q->with(['store', 'user'])->latest()->limit(20),
        ]);

        return view('pages.campaigns.show', compact('campaign'));
    }

    public function edit(Campaign $campaign): View
    {
        $this->authorize('update', $campaign);

        $campaign->load(['stores', 'products']);
        $stores = Store::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('pages.campaigns.edit', compact('campaign', 'stores', 'products'));
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('update', $campaign);

        $this->campaignService->updateCampaign($campaign, $request->validated());

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaign updated successfully.');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $this->authorize('delete', $campaign);

        $this->campaignService->deleteCampaign($campaign);

        return redirect()->route('campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }

    // ─── Campaign Tasks ───────────────────────────────────────

    public function tasks(Request $request): View
    {
        $tasks = $this->campaignService->listTasks(
            $request->only(['campaign_id', 'assigned_to', 'status', 'store_id']),
            $request->integer('per_page', 15)
        );

        $campaigns = Campaign::all(['id', 'name']);

        return view('pages.campaigns.tasks', compact('tasks', 'campaigns'));
    }

    public function showTask(CampaignTask $campaignTask): View
    {
        $campaignTask->load(['campaign', 'store', 'assignedUser', 'verifier', 'photos', 'entries']);

        return view('pages.campaigns.task-show', ['task' => $campaignTask]);
    }

    public function verifyTask(CampaignTask $campaignTask): RedirectResponse
    {
        $this->campaignService->verifyTask($campaignTask, auth()->id());

        return back()->with('success', 'Task verified successfully.');
    }

    public function rejectTask(Request $request, CampaignTask $campaignTask): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $this->campaignService->rejectTask($campaignTask, auth()->id(), $request->input('reason'));

        return back()->with('success', 'Task rejected.');
    }

    // ─── POSM Materials ───────────────────────────────────────

    public function materials(Request $request): View
    {
        $materials = $this->campaignService->listMaterials(
            $request->only(['search', 'is_active']),
            $request->integer('per_page', 15)
        );

        return view('pages.campaigns.materials', compact('materials'));
    }

    public function showMaterial(PosmMaterial $posmMaterial): View
    {
        $posmMaterial->load(['placements.store', 'placements.checkLogs']);

        return view('pages.campaigns.material-show', ['material' => $posmMaterial]);
    }
}
