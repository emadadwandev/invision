<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $service,
    ) {}

    public function index(Request $request): View
    {
        $period = $request->query('period', 'month');

        return view('pages.dashboard.index', [
            'user'       => $request->user(),
            'overview'   => $this->service->getOverviewKpis(),
            'sales'      => $this->service->getSalesKpis($period),
            'routes'     => $this->service->getRouteKpis($period),
            'campaigns'  => $this->service->getCampaignKpis(),
            'pos'        => $this->service->getPosKpis($period),
            'credits'    => $this->service->getCreditKpis(),
            'period'     => $period,
        ]);
    }

    public function storeInquiry(Request $request): View
    {
        $filters = $request->only(['search', 'category', 'rank', 'area_id']);

        return view('pages.inquiry.stores', [
            'stores'  => $this->service->getStoreInquiry($filters),
            'filters' => $filters,
        ]);
    }

    public function salesInquiry(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'store_id', 'user_id', 'date_from', 'date_to']);

        return view('pages.inquiry.sales', [
            'orders'  => $this->service->getSalesInquiry($filters),
            'filters' => $filters,
        ]);
    }

    public function routeInquiry(Request $request): View
    {
        $filters = $request->only(['status', 'user_id', 'date_from', 'date_to']);

        return view('pages.inquiry.routes', [
            'routes'  => $this->service->getRouteInquiry($filters),
            'filters' => $filters,
        ]);
    }
}
