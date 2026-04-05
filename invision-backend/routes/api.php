<?php

use App\Http\Controllers\Api\V1\AreaController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PosController;
use App\Http\Controllers\Api\V1\SalesController;
use App\Http\Controllers\Api\V1\RouteController;
use App\Http\Controllers\Api\V1\CommandCenterController;
use App\Http\Controllers\Api\V1\CompetitorController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\GeoFenceController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\StoreController;
use App\Http\Controllers\Api\V1\SyncController;
use App\Http\Controllers\Api\V1\CalendarController;
use App\Http\Controllers\Api\V1\PresentationController;
use App\Http\Controllers\Api\V1\SalesAreaController;
use App\Http\Controllers\Api\V1\AuditController;
use App\Http\Controllers\Api\V1\LocaleController;
use App\Http\Controllers\Api\V1\MfaController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Auth (public)
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.v1.auth.login');

    // Locale (public — no auth required)
    Route::get('/locales', [LocaleController::class, 'supported'])->name('api.v1.locales');
    Route::get('/locales/{locale}/translations', [LocaleController::class, 'translations'])->name('api.v1.locales.translations');

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');

        // Locale preference
        Route::put('/locale', [LocaleController::class, 'updatePreference'])->name('api.v1.locale.update');

        // Users
        Route::apiResource('users', UserController::class)->names('api.v1.users');
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('api.v1.users.toggle-active');

        // Teams
        Route::apiResource('teams', TeamController::class)->names('api.v1.teams');
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('api.v1.teams.add-member');
        Route::delete('/teams/{team}/members/{userId}', [TeamController::class, 'removeMember'])->name('api.v1.teams.remove-member');
        Route::post('/teams/transfer', [TeamController::class, 'transfer'])->name('api.v1.teams.transfer');
        Route::get('/teams-hierarchy', [TeamController::class, 'hierarchy'])->name('api.v1.teams.hierarchy');

        // Stores
        Route::apiResource('stores', StoreController::class)->names('api.v1.stores');
        Route::patch('/stores/{store}/toggle-active', [StoreController::class, 'toggleActive'])->name('api.v1.stores.toggle-active');
        Route::post('/stores/{store}/products', [StoreController::class, 'assignProducts'])->name('api.v1.stores.assign-products');
        Route::delete('/stores/{store}/products', [StoreController::class, 'removeProducts'])->name('api.v1.stores.remove-products');

        // Products
        Route::apiResource('products', ProductController::class)->names('api.v1.products');
        Route::prefix('product-categories')->name('api.v1.product-categories.')->group(function () {
            Route::get('/', [ProductController::class, 'categories'])->name('index');
            Route::get('/tree', [ProductController::class, 'categoryTree'])->name('tree');
            Route::post('/', [ProductController::class, 'storeCategory'])->name('store');
            Route::put('/{category}', [ProductController::class, 'updateCategory'])->name('update');
            Route::delete('/{category}', [ProductController::class, 'destroyCategory'])->name('destroy');
        });

        // Geography / Areas
        Route::prefix('geography')->name('api.v1.geography.')->group(function () {
            Route::get('/countries', [AreaController::class, 'countries'])->name('countries');
            Route::post('/countries', [AreaController::class, 'storeCountry'])->name('countries.store');
            Route::get('/cities', [AreaController::class, 'cities'])->name('cities');
            Route::post('/cities', [AreaController::class, 'storeCity'])->name('cities.store');
            Route::get('/districts', [AreaController::class, 'districts'])->name('districts');
            Route::post('/districts', [AreaController::class, 'storeDistrict'])->name('districts.store');
            Route::get('/sectors', [AreaController::class, 'sectors'])->name('sectors');
            Route::post('/sectors', [AreaController::class, 'storeSector'])->name('sectors.store');
            Route::get('/areas', [AreaController::class, 'areas'])->name('areas');
            Route::post('/areas', [AreaController::class, 'storeArea'])->name('areas.store');
            Route::get('/streets', [AreaController::class, 'streets'])->name('streets');
            Route::post('/streets', [AreaController::class, 'storeStreet'])->name('streets.store');
        });

        // Route Plans
        Route::apiResource('route-plans', RouteController::class)->names('api.v1.route-plans')->parameters(['route-plans' => 'routePlan']);
        Route::post('/route-plans/{routePlan}/stores', [RouteController::class, 'addStore'])->name('api.v1.route-plans.add-store');
        Route::delete('/route-plans/{routePlan}/stores/{storeId}', [RouteController::class, 'removeStore'])->name('api.v1.route-plans.remove-store');
        Route::put('/route-plans/{routePlan}/reorder', [RouteController::class, 'reorderStores'])->name('api.v1.route-plans.reorder');

        // Route Instances
        Route::get('/route-instances', [RouteController::class, 'instances'])->name('api.v1.route-instances.index');
        Route::post('/route-plans/{routePlan}/instances', [RouteController::class, 'createInstance'])->name('api.v1.route-instances.create');
        Route::get('/route-instances/{routeInstance}', [RouteController::class, 'showInstance'])->name('api.v1.route-instances.show');
        Route::post('/route-instances/{routeInstance}/start', [RouteController::class, 'startInstance'])->name('api.v1.route-instances.start');
        Route::post('/route-instances/{routeInstance}/complete', [RouteController::class, 'completeInstance'])->name('api.v1.route-instances.complete');

        // Store Visits
        Route::post('/visits/{storeVisit}/check-in', [RouteController::class, 'checkIn'])->name('api.v1.visits.check-in');
        Route::post('/visits/{storeVisit}/check-out', [RouteController::class, 'checkOut'])->name('api.v1.visits.check-out');
        Route::post('/visits/{storeVisit}/skip', [RouteController::class, 'skipVisit'])->name('api.v1.visits.skip');

        // GPS Tracking
        Route::post('/gps/log', [RouteController::class, 'logGps'])->name('api.v1.gps.log');
        Route::get('/gps/tracking', [RouteController::class, 'trackingLogs'])->name('api.v1.gps.tracking');

        // My Routes (Mobile)
        Route::get('/my-route/today', [RouteController::class, 'myRouteToday'])->name('api.v1.my-route.today');
        Route::get('/my-routes', [RouteController::class, 'myRoutes'])->name('api.v1.my-routes');

        // Campaigns
        Route::apiResource('campaigns', CampaignController::class)->names('api.v1.campaigns');

        // Campaign Tasks
        Route::get('/campaigns/{campaign}/tasks', [CampaignController::class, 'tasks'])->name('api.v1.campaigns.tasks');
        Route::post('/campaign-tasks', [CampaignController::class, 'storeTask'])->name('api.v1.campaign-tasks.store');
        Route::get('/campaign-tasks/{campaignTask}', [CampaignController::class, 'showTask'])->name('api.v1.campaign-tasks.show');
        Route::post('/campaign-tasks/{campaignTask}/complete', [CampaignController::class, 'completeTask'])->name('api.v1.campaign-tasks.complete');
        Route::post('/campaign-tasks/{campaignTask}/verify', [CampaignController::class, 'verifyTask'])->name('api.v1.campaign-tasks.verify');
        Route::post('/campaign-tasks/{campaignTask}/reject', [CampaignController::class, 'rejectTask'])->name('api.v1.campaign-tasks.reject');
        Route::post('/campaign-tasks/{campaignTask}/photos', [CampaignController::class, 'uploadTaskPhoto'])->name('api.v1.campaign-tasks.upload-photo');

        // Campaign Entries
        Route::get('/campaigns/{campaign}/entries', [CampaignController::class, 'entries'])->name('api.v1.campaigns.entries');
        Route::post('/campaign-entries', [CampaignController::class, 'storeEntry'])->name('api.v1.campaign-entries.store');

        // POSM Materials
        Route::get('/posm-materials', [CampaignController::class, 'materials'])->name('api.v1.posm-materials.index');
        Route::post('/posm-materials', [CampaignController::class, 'storeMaterial'])->name('api.v1.posm-materials.store');
        Route::get('/posm-materials/{posmMaterial}', [CampaignController::class, 'showMaterial'])->name('api.v1.posm-materials.show');
        Route::put('/posm-materials/{posmMaterial}', [CampaignController::class, 'updateMaterial'])->name('api.v1.posm-materials.update');
        Route::delete('/posm-materials/{posmMaterial}', [CampaignController::class, 'destroyMaterial'])->name('api.v1.posm-materials.destroy');

        // POSM Placements
        Route::get('/posm-placements', [CampaignController::class, 'placements'])->name('api.v1.posm-placements.index');
        Route::post('/posm-placements', [CampaignController::class, 'storePlacement'])->name('api.v1.posm-placements.store');
        Route::post('/posm-placements/{posmPlacement}/check', [CampaignController::class, 'checkPlacement'])->name('api.v1.posm-placements.check');

        // My Tasks (Mobile)
        Route::get('/my-tasks', [CampaignController::class, 'myTasks'])->name('api.v1.my-tasks');

        // Sales Orders
        Route::apiResource('sales-orders', SalesController::class)->names('api.v1.sales-orders')->parameters(['sales-orders' => 'salesOrder']);
        Route::post('/sales-orders/{salesOrder}/confirm', [SalesController::class, 'confirm'])->name('api.v1.sales-orders.confirm');
        Route::post('/sales-orders/{salesOrder}/deliver', [SalesController::class, 'deliver'])->name('api.v1.sales-orders.deliver');
        Route::post('/sales-orders/{salesOrder}/cancel', [SalesController::class, 'cancel'])->name('api.v1.sales-orders.cancel');
        Route::post('/sales-orders/{salesOrder}/items', [SalesController::class, 'addItem'])->name('api.v1.sales-orders.add-item');
        Route::delete('/sales-orders/{salesOrder}/items/{itemId}', [SalesController::class, 'removeItem'])->name('api.v1.sales-orders.remove-item');

        // Payments
        Route::get('/payments', [SalesController::class, 'payments'])->name('api.v1.payments.index');
        Route::post('/payments', [SalesController::class, 'recordPayment'])->name('api.v1.payments.store');

        // Credit Accounts
        Route::get('/credit-accounts', [SalesController::class, 'creditAccounts'])->name('api.v1.credit-accounts.index');
        Route::post('/credit-accounts', [SalesController::class, 'storeCreditAccount'])->name('api.v1.credit-accounts.store');
        Route::put('/credit-accounts/{creditAccount}', [SalesController::class, 'updateCreditAccount'])->name('api.v1.credit-accounts.update');
        Route::post('/credit-payments', [SalesController::class, 'creditPayment'])->name('api.v1.credit-payments.store');

        // Deposit Receipts
        Route::get('/deposit-receipts', [SalesController::class, 'depositReceipts'])->name('api.v1.deposit-receipts.index');
        Route::post('/deposit-receipts', [SalesController::class, 'storeDepositReceipt'])->name('api.v1.deposit-receipts.store');

        // Rebates
        Route::get('/rebates', [SalesController::class, 'rebates'])->name('api.v1.rebates.index');
        Route::post('/rebates', [SalesController::class, 'storeRebate'])->name('api.v1.rebates.store');
        Route::get('/rebates/{rebate}', [SalesController::class, 'showRebate'])->name('api.v1.rebates.show');
        Route::put('/rebates/{rebate}', [SalesController::class, 'updateRebate'])->name('api.v1.rebates.update');
        Route::delete('/rebates/{rebate}', [SalesController::class, 'destroyRebate'])->name('api.v1.rebates.destroy');
        Route::get('/applicable-rebates', [SalesController::class, 'applicableRebates'])->name('api.v1.rebates.applicable');

        // My Orders (Mobile)
        Route::get('/my-orders', [SalesController::class, 'myOrders'])->name('api.v1.my-orders');

        // POS Terminals
        Route::get('/pos-terminals', [PosController::class, 'terminals'])->name('api.v1.pos-terminals.index');
        Route::post('/pos-terminals', [PosController::class, 'storeTerminal'])->name('api.v1.pos-terminals.store');
        Route::get('/pos-terminals/{posTerminal}', [PosController::class, 'showTerminal'])->name('api.v1.pos-terminals.show');
        Route::put('/pos-terminals/{posTerminal}', [PosController::class, 'updateTerminal'])->name('api.v1.pos-terminals.update');
        Route::delete('/pos-terminals/{posTerminal}', [PosController::class, 'destroyTerminal'])->name('api.v1.pos-terminals.destroy');
        Route::post('/pos-terminals/{posTerminal}/sync', [PosController::class, 'syncTerminal'])->name('api.v1.pos-terminals.sync');

        // POS Transactions
        Route::get('/pos-transactions', [PosController::class, 'transactions'])->name('api.v1.pos-transactions.index');
        Route::post('/pos-transactions', [PosController::class, 'storeTransaction'])->name('api.v1.pos-transactions.store');
        Route::get('/pos-transactions/{posTransaction}', [PosController::class, 'showTransaction'])->name('api.v1.pos-transactions.show');
        Route::post('/pos-transactions/{posTransaction}/complete', [PosController::class, 'completeTransaction'])->name('api.v1.pos-transactions.complete');
        Route::post('/pos-transactions/{posTransaction}/void', [PosController::class, 'voidTransaction'])->name('api.v1.pos-transactions.void');
        Route::post('/pos-transactions/{posTransaction}/sync', [PosController::class, 'syncTransaction'])->name('api.v1.pos-transactions.sync');

        // Store Inventory
        Route::get('/store-inventory', [PosController::class, 'inventory'])->name('api.v1.store-inventory.index');
        Route::get('/stores/{storeId}/inventory', [PosController::class, 'storeInventory'])->name('api.v1.store-inventory.store');
        Route::put('/store-inventory/{storeInventory}', [PosController::class, 'updateInventory'])->name('api.v1.store-inventory.update');

        // Stock Movements
        Route::get('/stock-movements', [PosController::class, 'stockMovements'])->name('api.v1.stock-movements.index');
        Route::post('/stock-movements', [PosController::class, 'recordStockMovement'])->name('api.v1.stock-movements.store');

        // My Transactions (Mobile)
        Route::get('/my-transactions', [PosController::class, 'myTransactions'])->name('api.v1.my-transactions');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'notifications'])->name('api.v1.notifications.index');
        Route::post('/notifications', [NotificationController::class, 'sendNotification'])->name('api.v1.notifications.store');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('api.v1.notifications.read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('api.v1.notifications.mark-all-read');
        Route::delete('/notifications/{notification}', [NotificationController::class, 'destroyNotification'])->name('api.v1.notifications.destroy');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.v1.notifications.unread-count');
        Route::get('/my-notifications', [NotificationController::class, 'myNotifications'])->name('api.v1.my-notifications');

        // Messages
        Route::get('/messages', [NotificationController::class, 'messages'])->name('api.v1.messages.index');
        Route::get('/inbox', [NotificationController::class, 'inbox'])->name('api.v1.inbox');
        Route::post('/messages', [NotificationController::class, 'sendMessage'])->name('api.v1.messages.store');
        Route::get('/messages/{message}', [NotificationController::class, 'showMessage'])->name('api.v1.messages.show');
        Route::post('/messages/{message}/read', [NotificationController::class, 'markMessageRead'])->name('api.v1.messages.read');
        Route::post('/messages/{message}/archive', [NotificationController::class, 'archiveMessage'])->name('api.v1.messages.archive');
        Route::delete('/messages/{message}', [NotificationController::class, 'destroyMessage'])->name('api.v1.messages.destroy');

        // Task Assignments
        Route::get('/task-assignments', [NotificationController::class, 'tasks'])->name('api.v1.task-assignments.index');
        Route::post('/task-assignments', [NotificationController::class, 'storeTask'])->name('api.v1.task-assignments.store');
        Route::get('/task-assignments/{taskAssignment}', [NotificationController::class, 'showTask'])->name('api.v1.task-assignments.show');
        Route::put('/task-assignments/{taskAssignment}', [NotificationController::class, 'updateTask'])->name('api.v1.task-assignments.update');
        Route::post('/task-assignments/{taskAssignment}/complete', [NotificationController::class, 'completeTask'])->name('api.v1.task-assignments.complete');
        Route::post('/task-assignments/{taskAssignment}/verify', [NotificationController::class, 'verifyTask'])->name('api.v1.task-assignments.verify');
        Route::post('/task-assignments/{taskAssignment}/reject', [NotificationController::class, 'rejectTask'])->name('api.v1.task-assignments.reject');
        Route::delete('/task-assignments/{taskAssignment}', [NotificationController::class, 'destroyTask'])->name('api.v1.task-assignments.destroy');
        Route::get('/my-assigned-tasks', [NotificationController::class, 'myAssignedTasks'])->name('api.v1.my-assigned-tasks');

        // Command Center (Live Tracking)
        Route::get('/command-center/stats', [CommandCenterController::class, 'stats'])->name('api.v1.command-center.stats');
        Route::get('/command-center/field-force', [CommandCenterController::class, 'fieldForcePositions'])->name('api.v1.command-center.field-force');
        Route::get('/command-center/stores', [CommandCenterController::class, 'storeMapData'])->name('api.v1.command-center.stores');
        Route::get('/command-center/stores/{storeId}/inquiry', [CommandCenterController::class, 'storeInquiry'])->name('api.v1.command-center.store-inquiry');
        Route::get('/command-center/users/{userId}/activity', [CommandCenterController::class, 'userActivity'])->name('api.v1.command-center.user-activity');

        // Dashboard & KPIs
        Route::prefix('dashboard')->name('api.v1.dashboard.')->group(function () {
            Route::get('/overview', [DashboardController::class, 'overview'])->name('overview');
            Route::get('/sales', [DashboardController::class, 'sales'])->name('sales');
            Route::get('/routes', [DashboardController::class, 'routes'])->name('routes');
            Route::get('/campaigns', [DashboardController::class, 'campaigns'])->name('campaigns');
            Route::get('/pos', [DashboardController::class, 'pos'])->name('pos');
            Route::get('/credits', [DashboardController::class, 'credits'])->name('credits');
        });

        // Inquiry Screens
        Route::prefix('inquiry')->name('api.v1.inquiry.')->group(function () {
            Route::get('/stores', [DashboardController::class, 'storeInquiry'])->name('stores');
            Route::get('/sales', [DashboardController::class, 'salesInquiry'])->name('sales');
            Route::get('/routes', [DashboardController::class, 'routeInquiry'])->name('routes');
        });

        // Competitors
        Route::apiResource('competitors', CompetitorController::class)->names('api.v1.competitors');
        Route::prefix('competitor-products')->name('api.v1.competitor-products.')->group(function () {
            Route::get('/', [CompetitorController::class, 'productIndex'])->name('index');
            Route::post('/', [CompetitorController::class, 'productStore'])->name('store');
            Route::get('/{competitorProduct}', [CompetitorController::class, 'productShow'])->name('show');
            Route::put('/{competitorProduct}', [CompetitorController::class, 'productUpdate'])->name('update');
            Route::delete('/{competitorProduct}', [CompetitorController::class, 'productDestroy'])->name('destroy');
        });
        Route::prefix('competitor-observations')->name('api.v1.competitor-observations.')->group(function () {
            Route::get('/', [CompetitorController::class, 'observationIndex'])->name('index');
            Route::post('/', [CompetitorController::class, 'observationStore'])->name('store');
            Route::get('/{competitorObservation}', [CompetitorController::class, 'observationShow'])->name('show');
            Route::put('/{competitorObservation}', [CompetitorController::class, 'observationUpdate'])->name('update');
            Route::delete('/{competitorObservation}', [CompetitorController::class, 'observationDestroy'])->name('destroy');
        });
        Route::get('/visits/{storeVisitId}/competitor-observations', [CompetitorController::class, 'visitObservations'])->name('api.v1.visits.competitor-observations');
        Route::get('/competitor-analysis', [CompetitorController::class, 'analysis'])->name('api.v1.competitor-analysis');

        // Reports
        Route::prefix('reports')->name('api.v1.reports.')->group(function () {
            Route::get('/sell-through', [ReportController::class, 'sellThrough'])->name('sell-through');
            Route::get('/sell-out', [ReportController::class, 'sellOut'])->name('sell-out');
            Route::get('/sell-in', [ReportController::class, 'sellIn'])->name('sell-in');
            Route::get('/stock-movement', [ReportController::class, 'stockMovement'])->name('stock-movement');
            Route::get('/vendor-ranking', [ReportController::class, 'vendorRanking'])->name('vendor-ranking');
            Route::get('/sales-rep-performance', [ReportController::class, 'salesRepPerformance'])->name('sales-rep-performance');
            Route::get('/entities', [ReportController::class, 'entities'])->name('entities');
            Route::post('/build', [ReportController::class, 'buildReport'])->name('build');
            Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('export-excel');
            Route::get('/export/pdf', [ReportController::class, 'exportPdf'])->name('export-pdf');
        });

        // Geo-Fence & Duty Tracking
        Route::get('/geofence/settings', [GeoFenceController::class, 'settings'])->name('api.v1.geofence.settings');
        Route::put('/geofence/settings', [GeoFenceController::class, 'updateSettings'])->name('api.v1.geofence.settings.update');
        Route::post('/geofence/validate', [GeoFenceController::class, 'validate'])->name('api.v1.geofence.validate');
        Route::post('/duty/start', [GeoFenceController::class, 'startDuty'])->name('api.v1.duty.start');
        Route::post('/duty/end', [GeoFenceController::class, 'endDuty'])->name('api.v1.duty.end');
        Route::get('/duty/active', [GeoFenceController::class, 'activeDuty'])->name('api.v1.duty.active');
        Route::get('/duty/sessions', [GeoFenceController::class, 'dutySessions'])->name('api.v1.duty.sessions');

        // Sync (Offline Support)
        Route::prefix('sync')->name('api.v1.sync.')->group(function () {
            Route::get('/pull', [SyncController::class, 'pull'])->name('pull');
            Route::post('/push', [SyncController::class, 'push'])->name('push');
            Route::get('/status', [SyncController::class, 'status'])->name('status');
            Route::get('/conflicts', [SyncController::class, 'conflicts'])->name('conflicts');
            Route::post('/retry', [SyncController::class, 'retry'])->name('retry');
        });

        // Audit Trail
        Route::get('/audit-logs', [AuditController::class, 'index'])->name('api.v1.audit-logs.index');
        Route::get('/audit-logs/{id}', [AuditController::class, 'show'])->name('api.v1.audit-logs.show');

        // MFA (Multi-Factor Authentication)
        Route::prefix('mfa')->name('api.v1.mfa.')->group(function () {
            Route::get('/status', [MfaController::class, 'status'])->name('status');
            Route::post('/enable', [MfaController::class, 'enable'])->name('enable');
            Route::post('/confirm', [MfaController::class, 'confirm'])->name('confirm');
            Route::post('/verify', [MfaController::class, 'verify'])->name('verify');
            Route::post('/disable', [MfaController::class, 'disable'])->name('disable');
            Route::post('/recovery-codes', [MfaController::class, 'regenerateRecoveryCodes'])->name('recovery-codes');
        });

        // Calendar
        Route::prefix('calendar')->name('api.v1.calendar.')->group(function () {
            Route::get('/weeks', [CalendarController::class, 'weeks'])->name('weeks');
            Route::post('/weeks/generate', [CalendarController::class, 'generateWeeks'])->name('weeks.generate');
            Route::get('/holidays', [CalendarController::class, 'holidays'])->name('holidays');
            Route::post('/holidays', [CalendarController::class, 'storeHoliday'])->name('holidays.store');
            Route::put('/holidays/{id}', [CalendarController::class, 'updateHoliday'])->name('holidays.update');
            Route::delete('/holidays/{id}', [CalendarController::class, 'destroyHoliday'])->name('holidays.destroy');
            Route::get('/holidays/check', [CalendarController::class, 'checkHoliday'])->name('holidays.check');
            Route::get('/events', [CalendarController::class, 'events'])->name('events');
            Route::post('/events', [CalendarController::class, 'storeEvent'])->name('events.store');
            Route::get('/events/{id}', [CalendarController::class, 'showEvent'])->name('events.show');
            Route::put('/events/{id}', [CalendarController::class, 'updateEvent'])->name('events.update');
            Route::delete('/events/{id}', [CalendarController::class, 'destroyEvent'])->name('events.destroy');
        });

        // Sales Areas
        Route::prefix('sales-areas')->name('api.v1.sales-areas.')->group(function () {
            Route::get('/', [SalesAreaController::class, 'index'])->name('index');
            Route::get('/hierarchy', [SalesAreaController::class, 'hierarchy'])->name('hierarchy');
            Route::get('/my-areas', [SalesAreaController::class, 'myAreas'])->name('my-areas');
            Route::get('/my-stores', [SalesAreaController::class, 'myStores'])->name('my-stores');
            Route::post('/', [SalesAreaController::class, 'store'])->name('store');
            Route::get('/{id}', [SalesAreaController::class, 'show'])->name('show');
            Route::put('/{id}', [SalesAreaController::class, 'update'])->name('update');
            Route::delete('/{id}', [SalesAreaController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/stores', [SalesAreaController::class, 'assignStores'])->name('assign-stores');
            Route::post('/{areaId}/assignments', [SalesAreaController::class, 'addAssignment'])->name('add-assignment');
            Route::delete('/{areaId}/assignments/{assignmentId}', [SalesAreaController::class, 'removeAssignment'])->name('remove-assignment');
        });

        // Presentation & Export Tools
        Route::prefix('presentations')->name('api.v1.presentations.')->group(function () {
            Route::get('/templates', [PresentationController::class, 'presentationTemplates'])->name('templates.index');
            Route::post('/templates', [PresentationController::class, 'storePresentationTemplate'])->name('templates.store');
            Route::post('/generate', [PresentationController::class, 'generatePresentation'])->name('generate');
            Route::post('/generate-html', [PresentationController::class, 'presentationToHtml'])->name('generate-html');
        });

        // Report Templates & Export
        Route::prefix('report-templates')->name('api.v1.report-templates.')->group(function () {
            Route::get('/', [PresentationController::class, 'reportTemplates'])->name('index');
            Route::post('/', [PresentationController::class, 'storeReportTemplate'])->name('store');
            Route::get('/{reportTemplate}', [PresentationController::class, 'showReportTemplate'])->name('show');
            Route::put('/{reportTemplate}', [PresentationController::class, 'updateReportTemplate'])->name('update');
            Route::delete('/{reportTemplate}', [PresentationController::class, 'destroyReportTemplate'])->name('destroy');
            Route::post('/{reportTemplate}/generate', [PresentationController::class, 'generateFromTemplate'])->name('generate');
            Route::get('/{reportTemplate}/export/excel', [PresentationController::class, 'exportTemplateExcel'])->name('export.excel');
            Route::get('/{reportTemplate}/export/pdf', [PresentationController::class, 'exportTemplatePdf'])->name('export.pdf');
            Route::get('/{reportTemplate}/export/csv', [PresentationController::class, 'exportTemplateCsv'])->name('export.csv');
        });

        // Saved Exports
        Route::get('/saved-exports', [PresentationController::class, 'savedExports'])->name('api.v1.saved-exports.index');
        Route::delete('/saved-exports/{savedExport}', [PresentationController::class, 'destroySavedExport'])->name('api.v1.saved-exports.destroy');
    });
});
