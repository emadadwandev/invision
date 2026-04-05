<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CampaignController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PosController;
use App\Http\Controllers\Web\SalesController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\RouteController;
use App\Http\Controllers\Web\CommandCenterController;
use App\Http\Controllers\Web\CompetitorController;
use App\Http\Controllers\Web\GeographyController;
use App\Http\Controllers\Web\GeoFenceController;
use App\Http\Controllers\Web\AuditController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\StoreController;
use App\Http\Controllers\Web\TeamController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::resource('users', UserController::class);

    // Teams
    Route::resource('teams', TeamController::class);

    // Stores
    Route::resource('stores', StoreController::class);
    Route::get('stores/{store}/products', [StoreController::class, 'assignProductsView'])->name('stores.products.edit');
    Route::post('stores/{store}/products', [StoreController::class, 'syncProducts'])->name('stores.products.sync');

    // Geography (Areas, Zones, Districts, Cities)
    Route::prefix('geography')->name('geography.')->group(function () {
        Route::get('/', [GeographyController::class, 'index'])->name('index');

        // Cities
        Route::get('/cities/create', [GeographyController::class, 'createCity'])->name('cities.create');
        Route::post('/cities', [GeographyController::class, 'storeCity'])->name('cities.store');
        Route::put('/cities/{city}', [GeographyController::class, 'updateCity'])->name('cities.update');
        Route::delete('/cities/{city}', [GeographyController::class, 'destroyCity'])->name('cities.destroy');

        // Districts
        Route::get('/districts/create', [GeographyController::class, 'createDistrict'])->name('districts.create');
        Route::post('/districts', [GeographyController::class, 'storeDistrict'])->name('districts.store');
        Route::put('/districts/{district}', [GeographyController::class, 'updateDistrict'])->name('districts.update');
        Route::delete('/districts/{district}', [GeographyController::class, 'destroyDistrict'])->name('districts.destroy');

        // Sectors (Zones)
        Route::get('/sectors/create', [GeographyController::class, 'createSector'])->name('sectors.create');
        Route::post('/sectors', [GeographyController::class, 'storeSector'])->name('sectors.store');
        Route::put('/sectors/{sector}', [GeographyController::class, 'updateSector'])->name('sectors.update');
        Route::delete('/sectors/{sector}', [GeographyController::class, 'destroySector'])->name('sectors.destroy');

        // Areas
        Route::get('/areas/create', [GeographyController::class, 'createArea'])->name('areas.create');
        Route::post('/areas', [GeographyController::class, 'storeArea'])->name('areas.store');
        Route::put('/areas/{area}', [GeographyController::class, 'updateArea'])->name('areas.update');
        Route::delete('/areas/{area}', [GeographyController::class, 'destroyArea'])->name('areas.destroy');

        // JSON cascading dropdown endpoints
        Route::get('/countries/{country}/cities', [GeographyController::class, 'citiesByCountry'])->name('countries.cities');
        Route::get('/cities/{city}/districts', [GeographyController::class, 'districtsByCity'])->name('cities.districts');
        Route::get('/districts/{district}/sectors', [GeographyController::class, 'sectorsByDistrict'])->name('districts.sectors');
        Route::get('/sectors/{sector}/areas', [GeographyController::class, 'areasBySector'])->name('sectors.areas');
    });

    // Products
    Route::resource('products', ProductController::class);

    // Product Categories
    Route::prefix('product-categories')->name('product-categories.')->group(function () {
        Route::get('/', [ProductController::class, 'categories'])->name('index');
        Route::get('/create', [ProductController::class, 'createCategory'])->name('create');
        Route::post('/', [ProductController::class, 'storeCategory'])->name('store');
        Route::get('/{category}/edit', [ProductController::class, 'editCategory'])->name('edit');
        Route::put('/{category}', [ProductController::class, 'updateCategory'])->name('update');
        Route::delete('/{category}', [ProductController::class, 'destroyCategory'])->name('destroy');
    });

    // Route Plans
    Route::resource('routes', RouteController::class)->parameters(['routes' => 'route']);
    Route::post('/routes/{route}/instances', [RouteController::class, 'createInstance'])->name('routes.create-instance');

    // Route Instances
    Route::prefix('route-instances')->name('route-instances.')->group(function () {
        Route::get('/', [RouteController::class, 'instances'])->name('index');
        Route::get('/{routeInstance}', [RouteController::class, 'showInstance'])->name('show');
    });

    // GPS Tracking
    Route::get('/tracking', [RouteController::class, 'tracking'])->name('tracking.index');

    // Campaigns
    Route::resource('campaigns', CampaignController::class);
    Route::get('/campaign-tasks', [CampaignController::class, 'tasks'])->name('campaigns.tasks');
    Route::get('/campaign-tasks/{campaignTask}', [CampaignController::class, 'showTask'])->name('campaigns.task-show');
    Route::post('/campaign-tasks/{campaignTask}/verify', [CampaignController::class, 'verifyTask'])->name('campaigns.task-verify');
    Route::post('/campaign-tasks/{campaignTask}/reject', [CampaignController::class, 'rejectTask'])->name('campaigns.task-reject');

    // POSM Materials
    Route::get('/posm-materials', [CampaignController::class, 'materials'])->name('campaigns.materials');
    Route::get('/posm-materials/{posmMaterial}', [CampaignController::class, 'showMaterial'])->name('campaigns.material-show');

    // Sales Orders
    Route::resource('sales', SalesController::class)->parameters(['sales' => 'salesOrder']);
    Route::post('/sales/{salesOrder}/confirm', [SalesController::class, 'confirm'])->name('sales.confirm');
    Route::post('/sales/{salesOrder}/deliver', [SalesController::class, 'deliver'])->name('sales.deliver');
    Route::post('/sales/{salesOrder}/cancel', [SalesController::class, 'cancel'])->name('sales.cancel');

    // Payments
    Route::get('/payments', [SalesController::class, 'payments'])->name('sales.payments');
    Route::post('/payments', [SalesController::class, 'recordPayment'])->name('sales.record-payment');

    // Credit Accounts
    Route::get('/credit-accounts', [SalesController::class, 'creditAccounts'])->name('sales.credit-accounts');
    Route::get('/credit-accounts/{creditAccount}', [SalesController::class, 'showCreditAccount'])->name('sales.credit-account-show');

    // Rebates
    Route::get('/rebates', [SalesController::class, 'rebates'])->name('sales.rebates');
    Route::get('/rebates/create', [SalesController::class, 'createRebate'])->name('sales.rebate-create');
    Route::post('/rebates', [SalesController::class, 'storeRebate'])->name('sales.rebate-store');
    Route::get('/rebates/{rebate}/edit', [SalesController::class, 'editRebate'])->name('sales.rebate-edit');
    Route::put('/rebates/{rebate}', [SalesController::class, 'updateRebate'])->name('sales.rebate-update');
    Route::delete('/rebates/{rebate}', [SalesController::class, 'destroyRebate'])->name('sales.rebate-destroy');

    // POS Terminals
    Route::get('/pos/terminals', [PosController::class, 'terminals'])->name('pos.terminals');
    Route::get('/pos/terminals/create', [PosController::class, 'createTerminal'])->name('pos.terminal-create');
    Route::post('/pos/terminals', [PosController::class, 'storeTerminal'])->name('pos.terminal-store');
    Route::get('/pos/terminals/{posTerminal}/edit', [PosController::class, 'editTerminal'])->name('pos.terminal-edit');
    Route::put('/pos/terminals/{posTerminal}', [PosController::class, 'updateTerminal'])->name('pos.terminal-update');
    Route::delete('/pos/terminals/{posTerminal}', [PosController::class, 'destroyTerminal'])->name('pos.terminal-destroy');

    // POS Transactions
    Route::get('/pos/transactions', [PosController::class, 'transactions'])->name('pos.transactions');
    Route::get('/pos/transactions/create', [PosController::class, 'createTransaction'])->name('pos.transaction-create');
    Route::post('/pos/transactions', [PosController::class, 'storeTransaction'])->name('pos.transaction-store');
    Route::get('/pos/transactions/{posTransaction}', [PosController::class, 'showTransaction'])->name('pos.transaction-show');
    Route::post('/pos/transactions/{posTransaction}/complete', [PosController::class, 'completeTransaction'])->name('pos.transaction-complete');
    Route::post('/pos/transactions/{posTransaction}/void', [PosController::class, 'voidTransaction'])->name('pos.transaction-void');

    // Store Inventory
    Route::get('/pos/inventory', [PosController::class, 'inventory'])->name('pos.inventory');
    Route::put('/pos/inventory/{storeInventory}', [PosController::class, 'updateInventory'])->name('pos.inventory-update');

    // Stock Movements
    Route::get('/pos/stock-movements', [PosController::class, 'stockMovements'])->name('pos.stock-movements');
    Route::post('/pos/stock-movements', [PosController::class, 'recordStockMovement'])->name('pos.record-stock-movement');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'notifications'])->name('index');
        Route::get('/create', [NotificationController::class, 'createNotification'])->name('create');
        Route::post('/', [NotificationController::class, 'storeNotification'])->name('store');
        Route::delete('/{notification}', [NotificationController::class, 'destroyNotification'])->name('destroy');
    });

    // Messages
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [NotificationController::class, 'messages'])->name('index');
        Route::get('/compose', [NotificationController::class, 'composeMessage'])->name('compose');
        Route::post('/', [NotificationController::class, 'sendMessage'])->name('send');
        Route::get('/{message}', [NotificationController::class, 'showMessage'])->name('show');
        Route::delete('/{message}', [NotificationController::class, 'destroyMessage'])->name('destroy');
    });

    // Task Assignments
    Route::prefix('task-assignments')->name('task-assignments.')->group(function () {
        Route::get('/', [NotificationController::class, 'taskAssignments'])->name('index');
        Route::get('/create', [NotificationController::class, 'createTask'])->name('create');
        Route::post('/', [NotificationController::class, 'storeTask'])->name('store');
        Route::get('/{taskAssignment}', [NotificationController::class, 'showTask'])->name('show');
        Route::post('/{taskAssignment}/verify', [NotificationController::class, 'verifyTask'])->name('verify');
        Route::post('/{taskAssignment}/reject', [NotificationController::class, 'rejectTask'])->name('reject');
        Route::delete('/{taskAssignment}', [NotificationController::class, 'destroyTask'])->name('destroy');
    });

    // Command Center (Live Tracking)
    Route::prefix('command-center')->name('command-center.')->group(function () {
        Route::get('/', [CommandCenterController::class, 'index'])->name('index');
        Route::get('/field-force-json', [CommandCenterController::class, 'fieldForcePositionsJson'])->name('field-force-json');
        Route::get('/stores/{storeId}/inquiry', [CommandCenterController::class, 'storeInquiry'])->name('store-inquiry');
        Route::get('/users/{userId}/activity', [CommandCenterController::class, 'userActivity'])->name('user-activity');
    });

    // Inquiry Screens
    Route::prefix('inquiry')->name('inquiry.')->group(function () {
        Route::get('/stores', [DashboardController::class, 'storeInquiry'])->name('stores');
        Route::get('/sales', [DashboardController::class, 'salesInquiry'])->name('sales');
        Route::get('/routes', [DashboardController::class, 'routeInquiry'])->name('routes');
    });

    // Competitors
    Route::resource('competitors', CompetitorController::class);
    Route::get('/competitors-products', [CompetitorController::class, 'products'])->name('competitors.products');
    Route::get('/competitors-products/create', [CompetitorController::class, 'createProduct'])->name('competitors.products.create');
    Route::post('/competitors-products', [CompetitorController::class, 'storeProduct'])->name('competitors.products.store');
    Route::get('/competitors-observations', [CompetitorController::class, 'observations'])->name('competitors.observations');
    Route::get('/competitors-analysis', [CompetitorController::class, 'analysis'])->name('competitors.analysis');

    // Geo-Fence & Duty Tracking
    Route::get('/geofence/settings', [GeoFenceController::class, 'settings'])->name('geofence.settings');
    Route::put('/geofence/settings', [GeoFenceController::class, 'updateSettings'])->name('geofence.settings.update');
    Route::get('/geofence/duty-sessions', [GeoFenceController::class, 'dutySessions'])->name('geofence.duty-sessions');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/{type}', [ReportController::class, 'show'])->name('show')
            ->whereIn('type', ['sell-through', 'sell-out', 'sell-in', 'stock-movement', 'vendor-ranking', 'sales-rep-performance']);
        Route::get('/{type}/export/excel', [ReportController::class, 'exportExcel'])->name('export-excel');
        Route::get('/{type}/export/pdf', [ReportController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/builder/index', [ReportController::class, 'builder'])->name('builder');
        Route::post('/builder/run', [ReportController::class, 'runBuilder'])->name('builder.run');
        Route::post('/builder/export/excel', [ReportController::class, 'exportBuilderExcel'])->name('builder.export-excel');
        Route::post('/builder/export/pdf', [ReportController::class, 'exportBuilderPdf'])->name('builder.export-pdf');
    });

    // Audit Trail
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/audit/{id}', [AuditController::class, 'show'])->name('audit.show');
});
