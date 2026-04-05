# Changelog - Invision Mobile

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- **2026-04-05**: Role-based home screen routing
  - Field force roles (`field_force`, `sales_representative`, `promoter`, `merchandiser`) are redirected to `/field-force-home` after login instead of the admin dashboard
  - Admin/team-leader roles continue to land on `/dashboard`
  - Cross-role direct navigation is blocked by router redirect guards
- **2026-04-05**: `FieldForceHomePage` — dedicated home screen for field force users
  - **Today tab**: Duty Start/End button with live GPS status (uses `GpsTrackingController`), today's route with progress bar, store visit cards
  - Store visit cards: visit order number, store name/address/area, status chip (Pending / Checked In / Completed / Skipped), check-in/check-out times and duration, action buttons (Navigate → Google Maps, Check In, Check Out, Create Order, Skip)
  - Check-in/check-out uses real device GPS via `LocationService.getCurrentPosition()`
  - **Notifications tab**: notification list with type icon/color, unread badge, mark-all-read
  - **Profile tab**: avatar, name, role, duty status indicator, language/sync/orders links, logout with confirmation
  - Notifications badge on bottom nav shows unread count via `unreadNotificationCountProvider`

### Added
- **2026-04-05**: Android permissions added to `AndroidManifest.xml`
  - **Location**: `ACCESS_FINE_LOCATION`, `ACCESS_COARSE_LOCATION`, `ACCESS_BACKGROUND_LOCATION` for GPS tracking
  - **Camera**: `CAMERA` permission + hardware feature declarations (not required, graceful fallback)
  - **Notifications**: `POST_NOTIFICATIONS` (Android 13+), `RECEIVE_BOOT_COMPLETED`, `VIBRATE`
  - **Phone / Network**: `INTERNET`, `READ_PHONE_STATE`

### Fixed
- **2026-04-05**: Login "Something went wrong" error — `AuthUser.fromJson` used `json['name']` but backend `UserResource` returns `full_name`/`first_name`/`last_name`
- **2026-04-05**: `me()` endpoint parsed `response.data['data']` but backend returns `response.data['user']`

### Added
- **2026-04-05**: Full authentication layer implementation
  - Auth models: `AuthUser`, `AuthState` data classes
  - Auth repository: login/logout/me API calls with Sanctum token support
  - Token persistence via `shared_preferences`
  - `AuthNotifier` state provider with session restore on app startup
  - Auth endpoints (`/auth/login`, `/auth/logout`, `/auth/me`) in `ApiEndpoints`

### Changed
- **2026-04-05**: Wired login page to real backend API (email/password → Sanctum token)
  - Loading state, error messages, keyboard submit support
  - Converted from `StatelessWidget` to `ConsumerStatefulWidget`
- **2026-04-05**: Shared `ApiClient` via Riverpod `apiClientProvider` with `setToken()`/`clearToken()`
  - All 15 repository providers now inject the shared `ApiClient` instance
  - Auth token (`Bearer`) automatically included in all API requests
- **2026-04-05**: Router converted to `appRouterProvider` with auth redirect guard
  - Unauthenticated users redirected to `/login`
  - Authenticated users redirected from `/login` to `/dashboard`
- **2026-04-05**: `main.dart` uses `ProviderContainer` for pre-app session restore

### Fixed
- **2026-04-05**: All API calls returning 401 Unauthorized — auth token was never sent

### Added (previous)
- **2026-04-01**: Initial Flutter mobile scaffold (Android/iOS)
- **2026-04-01**: Feature-first starter structure under `lib/src` (core, auth, dashboard)
- **2026-04-01**: GoRouter route setup (`/login`, `/dashboard`) and starter pages
- **2026-04-01**: Riverpod + Dio dependencies and base API client setup
- **2026-04-01**: Starter widget test aligned with `InvisionApp`
- **2026-04-01**: Phase 2 — Store & Product mobile features
  - Enums: `StoreCategory`, `StoreRank` in `core/enums/`
  - API endpoints constants (`ApiEndpoints`) for stores, products, product categories
  - Store feature: `Store`/`StoreContact` models, `StoreRepository`, Riverpod providers (`storesProvider`, `storeDetailProvider`), `StoreListPage` (with search), `StoreDetailPage`
  - Product feature: `Product`/`PriceLevel`/`ProductCategory` models, `ProductRepository`, Riverpod providers (`productsProvider`, `productDetailProvider`, `productCategoriesProvider`), `ProductListPage` (with search), `ProductDetailPage`
  - GoRouter: 4 new routes (`/stores`, `/stores/:id`, `/products`, `/products/:id`)
  - Dashboard: Quick Access tiles for Stores and Products navigation

## [Phase 3] - Route Management & GPS Tracking

### Added
- **2026-04-01**: Phase 3 — Route Management & GPS Tracking mobile features
  - Enums: `RouteStatus`, `VisitStatus` in `core/enums/`
  - API endpoints: route plans, instances, visits (check-in/out/skip), GPS log, my-routes
  - Route feature: `RoutePlan`/`RoutePlanStore` models, `RouteInstance`/`StoreVisit` models
  - `RouteRepository`: plan listing/detail, my-route-today, instances, check-in/out/skip, GPS logging
  - Riverpod providers: `routePlansProvider`, `routePlanDetailProvider`, `myRouteTodayProvider`, `myRoutesProvider`, `routeInstanceDetailProvider`
  - `RouteListPage`: route plans list with search
  - `RouteDetailPage`: plan detail with store sequence
  - `MyRoutePage`: today's route with visit list, check-in/out/skip actions, progress tracking
  - GoRouter: 3 new routes (`/routes`, `/routes/:id`, `/my-route`)
  - Dashboard: "My Route Today" and "Route Plans" quick access tiles

## [Phase 4] - Campaigns & Marketing Materials

### Added
- **2026-04-01**: Phase 4 — Campaigns & Marketing Materials mobile features
  - Enums: `CampaignStatus`, `CampaignType`, `TaskStatus` in `core/enums/`
  - API endpoints: campaigns CRUD, campaign tasks, entries, POSM materials/placements, my-tasks
  - Campaign feature: `Campaign` model (with budget, type, status, targeting counts), `CampaignTask`/`TaskPhoto` models
  - `CampaignRepository`: campaign listing/detail with filters, campaign tasks, task completion, my-tasks
  - Riverpod providers: `campaignsProvider` (with `CampaignFilter`), `campaignDetailProvider`, `campaignTasksProvider`, `myTasksProvider`
  - `CampaignListPage`: campaigns list with search, status chips, budget display
  - `CampaignDetailPage`: campaign detail with info card (period, budget, utilization, tasks/entries counts)
  - `MyTasksPage`: personal task list with status filter, task cards with campaign/store/instructions
  - GoRouter: 3 new routes (`/campaigns`, `/campaigns/:id`, `/my-tasks`)
  - Dashboard: "Campaigns" and "My Tasks" quick access tiles

## [Phase 5] - Sales & Collection

### Added
- **2026-04-01**: Phase 5 — Sales & Collection mobile features
  - Enums: `OrderStatus`, `PaymentMethod`, `PaymentStatus` in `core/enums/`
  - API endpoints: sales orders CRUD, confirm/deliver/cancel, items, payments, credit accounts, deposit receipts, rebates, my-orders
  - Sales feature: `SalesOrder`/`SalesOrderItem` models, `Payment` model, `CreditAccount` model
  - `SalesRepository`: order listing/detail/create, confirm/deliver/cancel actions, my-orders, payments, credit accounts
  - Riverpod providers: `salesOrdersProvider` (with `SalesOrderFilter`), `salesOrderDetailProvider`, `myOrdersProvider`, `paymentsProvider` (with `PaymentFilter`), `creditAccountsProvider`
  - `SalesOrderListPage`: orders list with search, status chips, amount display
  - `SalesOrderDetailPage`: order detail with items, totals, balance due, confirm/deliver/cancel actions
  - `MyOrdersPage`: personal orders with status filter, balance due indicator
  - `CreateOrderPage`: create order form with dynamic item entries (product ID, barcode, qty, unit price, discount)
  - GoRouter: 4 new routes (`/sales`, `/sales/create`, `/sales/:id`, `/my-orders`)
  - Dashboard: "Sales Orders" and "My Orders" quick access tiles

## [Phase 6] - POS Sales Operation

### Added
- **2026-04-01**: Phase 6 — POS Sales Operation mobile features
  - Enums: `PosTransactionType`, `PosTransactionStatus`, `StockMovementType` in `core/enums/`
  - API endpoints: POS terminals CRUD + sync, POS transactions CRUD + complete/void/sync, store inventory, stock movements, my-transactions
  - POS feature: `PosTerminal`, `PosTransaction`/`PosTransactionItem`, `StoreInventoryItem` models
  - `PosRepository`: transaction listing/detail/create, complete/void actions, my-transactions, terminals listing, inventory listing
  - Riverpod providers: `posTransactionsProvider` (with `PosTransactionFilter`), `posTransactionDetailProvider`, `myTransactionsProvider`, `posTerminalsProvider`, `storeInventoryProvider` (with `InventoryFilter`)
  - `PosTransactionListPage`: transactions list with search, type/status chips, amount display
  - `PosTransactionDetailPage`: transaction detail with items, subtotal/tax/total summary, store/terminal/user info
  - `StoreInventoryPage`: inventory list with search, on-shelf/warehouse quantities, low-stock indicator (red <10)
  - `MyTransactionsPage`: personal POS transactions with type filter (sell out/sell through/return)
  - GoRouter: 4 new routes (`/pos`, `/pos/:id`, `/inventory`, `/my-transactions`)
  - Dashboard: "POS Transactions", "Store Inventory", and "My POS Transactions" quick access tiles

## [Phase 7] - Notifications & Messaging

### Added
- **2026-04-01**: Phase 7 — Notifications & Messaging mobile features
  - Enums: `NotificationType`, `NotificationPriority`, `TaskAssignmentStatus` in `core/enums/`
  - API endpoints: notifications, my-notifications, unread-count, mark-read, mark-all-read; messages, inbox, message detail, mark-message-read, archive; task-assignments, my-assigned-tasks, complete/verify/reject
  - Notifications feature: `NotificationItem`, `MessageItem`/`MessageRecipientItem`, `TaskAssignment` models
  - `NotificationsRepository`: my-notifications, unread count, mark-read/all, inbox with search/archive, message detail/send, mark-message-read, archive; my-assigned-tasks with status filter, task detail, complete task with proof
  - Riverpod providers: `myNotificationsProvider`, `unreadNotificationCountProvider`, `inboxProvider` (with `InboxFilter`), `messageDetailProvider`, `myAssignedTasksProvider`, `taskAssignmentDetailProvider`
  - `NotificationsPage`: personal notifications list with type/priority chips, mark-read action, mark-all-read bulk action, unread highlighting
  - `InboxPage`: message inbox with search, group/direct badges, sender info
  - `MessageDetailPage`: message detail with recipients read-status chips, auto-mark-read on view
  - `MyAssignedTasksPage`: assigned tasks with status filter chips, priority/status badges, due date with overdue indicator
  - `TaskAssignmentDetailPage`: task detail with description, assigner/assignee info, due date, complete task form with notes
  - GoRouter: 6 new routes (`/notifications`, `/inbox`, `/messages/:id`, `/assigned-tasks`, `/assigned-tasks/:id`)
  - Dashboard: "Notifications", "Inbox", and "My Assigned Tasks" quick access tiles

## [Phase 8] - Command Center (Live Tracking) & Mapbox Migration

### Added
- **2026-04-01**: Phase 8 — Command Center (Live Tracking) mobile features
  - Dependencies: `flutter_map` ^7.0.2, `latlong2` ^0.9.1, `url_launcher` ^6.3.1
  - Models: `CommandCenterStats`, `FieldForcePosition` (with `hasLocation`), `StoreMapItem` (with `StoreSalesSummary`/`StoreInventorySummary`/`StoreCreditSummary`), `UserActivity` (with `UserActivityInfo`/`UserRouteInfo`/`UserVisitInfo`/`GpsTrailPoint`)
  - API endpoints: 5 new command center endpoints (stats, field-force, stores, store inquiry, user activity)
  - `CommandCenterRepository`: stats, field force positions, store map data, store inquiry, user activity
  - Riverpod providers: `commandCenterStatsProvider`, `fieldForcePositionsProvider`, `storeMapDataProvider`, `storeInquiryProvider`, `userActivityProvider`
  - `CommandCenterPage`: full-screen Mapbox-tiled map with store/field-force markers, stats bar, draggable field force bottom sheet with online/offline sorting, toggle markers, auto-refresh positions every 30s, store detail bottom sheet (sales/inventory/credit), user detail bottom sheet
  - `UserActivityPage`: user info card, Mapbox-tiled map with GPS trail polyline, start/current position markers, store visits list with status icons
  - GoRouter: 2 new routes (`/command-center`, `/command-center/user/:id`)
  - Dashboard: "Command Center" quick access tile

## [Phase 9] - Dashboards, KPIs & Inquiry Screens

### Added
- **2026-04-01**: Phase 9 — Dashboards, KPIs & Inquiry Screens mobile features
  - Dependency: `intl` ^0.20.2 (currency/number formatting)
  - API endpoints: 9 new endpoints — dashboard overview/sales/routes/campaigns/pos/credits, inquiry stores/sales/routes
  - Models: `OverviewKpi` (10 fields), `SalesKpi` (8 fields incl top stores/reps), `RouteKpi` (8 fields), `CampaignKpi` (3 fields), `RankedItem` (name/sales/count), `StoreInquiryItem` (12 fields), `SalesInquiryItem` (9 fields), `RouteInquiryItem` (10 fields)
  - `DashboardRepository`: 7 methods — overview, sales KPI, route KPI, campaign KPI, store/sales/route inquiry with filter params
  - Riverpod providers: `overviewKpiProvider`, `salesKpiProvider` (period family), `routeKpiProvider` (period family), `campaignKpiProvider`, `storeInquiryProvider` (with `StoreInquiryFilter`), `salesInquiryProvider` (with `SalesInquiryFilter`), `routeInquiryProvider` (with `RouteInquiryFilter`)
  - `DashboardPage`: complete redesign with period selector (PopupMenuButton), overview KPI cards (users, field force, online, stores, campaigns), gradient today-stats card (visits, orders, sales, collections), sales section (revenue, avg order, top stores list), routes section (completion bars, avg duration), campaigns section (budget utilization), inquiry navigation tiles, 14 quick access chips
  - `StoreInquiryPage`: search + category/rank dropdown filters, card-based store list with orders/sales/stock/credit
  - `SalesInquiryPage`: search + status dropdown filter, card-based order list with status badge/totals/balance
  - `RouteInquiryPage`: status dropdown filter, card-based route list with progress bar/visits/distance
  - GoRouter: 3 new routes (`/inquiry/stores`, `/inquiry/sales`, `/inquiry/routes`)

## [Phase 10] - Reporting & Export Engine

### Added
- **2026-04-01**: Phase 10 — Reporting & Export Engine mobile features
  - API endpoints: 10 new endpoints — 6 fixed reports (sell-through, sell-out, sell-in, stock-movement, vendor-ranking, sales-rep-performance), entities metadata, build dynamic report, export Excel, export PDF
  - Models: `ReportData` (title/period/generated/rows with computed columns), `ReportEntity` (key/label/columns/groupByOptions/aggregations), `FixedReportType` enum (6 report types with slug/label/description)
  - `ReportRepository`: `getFixedReport()`, `getEntities()`, `buildReport()`, `getExcelExportUrl()`, `getPdfExportUrl()`
  - Riverpod providers: `reportRepositoryProvider`, `fixedReportProvider` (with `FixedReportFilter`), `reportEntitiesProvider`, `dynamicReportProvider` (with `DynamicReportFilter`)
  - `ReportsListPage`: 6 fixed report cards with colored icons + descriptions, AppBar builder button
  - `ReportDetailPage`: date range picker, DataTable with row numbers + formatted numbers, export popup menu (Excel/PDF via url_launcher)
  - `ReportBuilderPage`: entity/groupBy/direction/limit selectors loaded from entities metadata, run report + results DataTable
  - GoRouter: 3 new routes (`/reports`, `/reports/builder`, `/reports/:slug`)
  - Dashboard: added "Reports" quick access chip

## [Phase 11] - Competition Tracking & QR Code Scanning

### Added
- **2026-04-01**: Phase 11 — Competition Tracking & QR Code Scanning mobile features
  - Package: `mobile_scanner` ^6.0.2 for QR/barcode scanning via device camera
  - API endpoints: 7 new constants — competitors, competitor by ID, competitor products, competitor product by ID, competitor observations, competitor observation by ID, visit competitor observations, competitor analysis
  - Models: `Competitor` (id/name/description/logoPath/isActive/productsCount/observationsCount), `CompetitorProduct` (id/competitorId/name/sku/barcode/category/isActive), `CompetitorObservation` (id/storeVisitId/competitorId/observationType/quantity/price/notes/observedAt), `CompetitorAnalysisItem` (competitorId/competitorName/observationType/avgPrice/totalQuantity/observationCount), `AnalysisType` enum
  - `CompetitorRepository`: `getCompetitors()`, `getCompetitor()`, `getProducts()`, `getObservations()`, `createObservation()`, `getVisitObservations()`, `getAnalysis()`
  - Riverpod providers: `competitorRepositoryProvider`, `competitorsProvider`, `competitorDetailProvider`, `competitorProductsProvider`, `competitorObservationsProvider`, `visitObservationsProvider`, `competitorAnalysisProvider` + `ObservationFilter` and `AnalysisFilter` classes
  - `CompetitorListPage`: search bar, ListView of competitor cards with name/description/products count/observations count/active status badge
  - `CompetitorDetailPage`: header with avatar, stats row (products/observations), description card, scrollable products list
  - `CompetitorAnalysisPage`: date range picker filter, analysis cards per competitor with color-coded observation type chips
  - `AddObservationPage`: ChoiceChip type selector (7 types), cascading competitor/product dropdowns, quantity/price fields, notes, form validation and submit
  - `QrScannerPage`: MobileScannerController with camera view, scan overlay, torch toggle, camera switch, manual entry dialog, returns scanned value via `context.pop()`
  - GoRouter: 5 new routes (`/competitors`, `/competitors/analysis`, `/competitors/:id`, `/competitors/observe/:storeId`, `/scanner`)

## [Phase 12] - GPS & Geo-Fencing

### Added
- **2026-04-01**: Phase 12 — GPS Tracking, Geo-Fencing & Duty Management mobile features
  - Packages: `geolocator` ^13.0.2 (device GPS coordinates, permission handling, distance calculation, background tracking), `flutter_local_notifications` ^18.0.1 (foreground service notifications)
  - API endpoints: 6 new constants — geofenceSettings, geofenceValidate, dutyStart, dutyEnd, dutyActive, dutySessions
  - `LocationService` (singleton): ensurePermissions, getCurrentPosition, distanceBetween, isWithinGeoFence, startTracking (with AndroidSettings foreground notification), stopTracking, openLocationSettings, openAppSettings
  - `GpsTrackingRepository`: getGeofenceSettings, getActiveDuty, startDuty, endDuty
  - Models: `GeofenceSettings` (8 fields with defaults), `DutyStatus` (active flag + session data)
  - Riverpod providers: `locationServiceProvider`, `currentPositionProvider`, `geofenceSettingsProvider`, `activeDutyProvider`, `gpsTrackingControllerProvider` (StateNotifier)
  - `GpsTrackingController`: startDuty (permissions → GPS → API → tracking loop), endDuty (flush logs → stop → API), auto-batch GPS logs (every 60s or 10 logs), pending log retry on failure
  - `DutyTrackingPage`: on/off duty status card, GPS stats (tracking status, position, pending logs, speed), start/end duty button with confirmation dialog
  - `GeoFenceCheckPage`: proximity check before check-in, shows distance vs radius, proceed/retry/settings actions
  - GoRouter: 2 new routes (`/duty`, `/geofence-check` with extra params)

### Added
- **2026-04-01**: Phase 13 — WebSocket Integration (Real-time)
  - Package: `web_socket_channel` ^3.0.2 — WebSocket client for Laravel Reverb
  - `WebSocketService`: persistent connection to Reverb, private channel subscription, event listeners, auto-reconnect with 5s backoff, ping keep-alive
  - `ReverbConfig` / `ReverbEvent` models for WebSocket configuration and events
  - Real-time providers:
    - `webSocketServiceProvider` — singleton WebSocket service
    - `wsConnectionProvider` — connection state stream
    - `liveGpsTrackingProvider` — real-time GPS position updates per tenant
    - `liveNotificationProvider` — real-time push notifications per user
    - `liveVisitStatusProvider` — real-time visit check-in/check-out per tenant
    - `liveDutyStatusProvider` — real-time duty on/off status per tenant
  - Command Center: WebSocket integration for live GPS marker updates, connection indicator in AppBar

### Added
- **2026-04-01**: Phase 14 — Offline Support & Sync
  - Packages: `sqflite` ^2.4.2 (local SQLite), `path` ^1.9.1, `connectivity_plus` ^6.1.4 (network monitoring), `uuid` ^4.5.1 (idempotency keys)
  - `OfflineDatabase` (singleton): 6 tables — sync_queue, cached_stores, cached_products, cached_route_plans, cached_notifications, gps_log_buffer, sync_meta. Full CRUD for queue, cache, GPS buffer, metadata.
  - `SyncEngine`: bidirectional sync with connectivity monitoring, auto-reconnect push on online, periodic 60s sync, GPS buffer push, delta pull with local caching, SyncStatus stream
  - API endpoints: 5 new constants — syncPull, syncPush, syncStatus, syncConflicts, syncRetry
  - Riverpod providers: `connectivityProvider`, `isOnlineProvider`, `offlineDatabaseProvider`, `syncEngineProvider`, `syncStatusProvider`, `pendingActionsCountProvider`
  - `SyncStatusPage`: connection status card, sync activity card, pending actions counter, force sync & retry failed buttons
  - GoRouter: 1 new route (`/sync`)

### Added
- **2026-04-02**: Phase 15 — MFA Setup (Two-Factor Authentication)
  - `MfaRepository`: API client for MFA endpoints — getStatus, enable, confirm, verify, disable, regenerateRecoveryCodes
  - Models: `MfaStatus` (enabled, confirmed, recovery codes remaining), `MfaEnableResult` (secret, QR URI, recovery codes)
  - API endpoints: 6 new constants — mfaStatus, mfaEnable, mfaConfirm, mfaVerify, mfaDisable, mfaRecoveryCodes
  - Riverpod providers: `mfaRepositoryProvider`, `mfaStatusProvider`
  - `MfaSetupPage`: MFA status card (enabled/disabled visual indicator), enable flow (generate secret → display for authenticator app → 6-digit confirmation), recovery codes display (chips with copy-all), disable flow (password confirmation), regenerate recovery codes button
  - GoRouter: 1 new route (`/mfa-setup`)

### Added
- **2026-04-02**: Phase 16 — Multi-Language & Localization (i18n)
  - Dependency: `flutter_localizations` SDK — Material/Cupertino/Widgets localization delegates
  - ARB files: 3 locales (en, ar, fr) — `app_en.arb`, `app_ar.arb`, `app_fr.arb` with 110+ translation keys
  - Generated: `AppLocalizations` class via `flutter gen-l10n` with `l10n.yaml` config
  - `localeProvider` (StateProvider): app-wide locale state management
  - `InvisionApp` updated from StatelessWidget to ConsumerWidget — reads `localeProvider`, sets `locale`, `supportedLocales`, `localizationsDelegates`
  - `LanguageSettingsPage`: language picker with radio list (English, Arabic, French), visual selection state
  - API endpoints: 3 new constants — locales, localeTranslations, localePreference
  - GoRouter: 1 new route (`/language`)

### Added
- **2026-04-02**: Phase 17 — Calendar & Sales Area Management
  - Models: `CalendarEventModel` (id, title, description, startAt, endAt, allDay, type, color), `HolidayModel` (name, date, type, isRecurring), `SalesAreaModel` (hierarchical with children, managerName, storeCount)
  - `CalendarRepository`: getEvents (with date/type filters), getHolidays (by year), getSalesAreas, getSalesAreasHierarchy, getSalesArea
  - Riverpod providers: `calendarEventsProvider`, `holidaysProvider`, `salesAreasProvider`, `salesAreasHierarchyProvider`, `salesAreaDetailProvider`
  - `CalendarPage`: tabbed view (Events + Holidays), event cards with type-based coloring, holiday tiles with recurrence indicator
  - `SalesAreaListPage`: hierarchical area display with recursive child rendering, manager/store info
  - `SalesAreaDetailPage`: area info card, sub-areas list, manager and store count
  - API endpoints: 9 new constants — calendarWeeks/Generate/Holidays/HolidayCheck/Events/Event, salesAreas/Hierarchy/myAreas/myAreaStores/salesArea/salesAreaStores/salesAreaAssignments
  - GoRouter: 3 new routes (`/calendar`, `/sales-areas`, `/sales-areas/:id`)

### Phase 18 — Presentation Tools & Advanced Export
- Models: `ReportTemplateModel`, `PresentationTemplateModel`, `SavedExportModel`, `PresentationDataModel`, `SlideModel` — full export/presentation data structures
- Repository: `ExportRepository` — report template CRUD, generate from template, export to Excel/PDF/CSV, presentation templates, generate market review & custom presentations, saved exports management
- Providers: `exportRepositoryProvider`, `reportTemplatesProvider`, `presentationTemplatesProvider`, `savedExportsProvider`, `marketReviewProvider`
- Pages:
  - `ExportDashboardPage`: quick action cards (Market Review, Report Templates, Presentation Templates, Export History), recent exports list
  - `MarketReviewPage`: slideshow viewer with PageView, period selector (week/month/quarter/year), slide layouts (title, KPI grid, table, two-column), navigation dots
  - `ReportTemplatesPage`: template list with type icons, create dialog, action buttons (Generate, Excel, PDF, CSV), favorites/shared indicators
  - `SavedExportsPage`: export history with format icons, file size, delete with confirmation
- API endpoints: 11 new constants — presentationTemplates/Generate/GenerateHtml, reportTemplates/Template/Generate/ExportExcel/ExportPdf/ExportCsv, savedExports/savedExport
- GoRouter: 4 new routes (`/exports`, `/presentations/market-review`, `/report-templates`, `/saved-exports`)

### Phase 19 — Testing & Polish
- Unit Tests: `export_models_test.dart` (8 tests) — ReportTemplateModel fromJson/toJson, SavedExportModel fromJson/formattedSize/formatLabel, PresentationDataModel with slides/empty slides, PresentationTemplateModel fromJson
- All 8 Flutter tests passing
- Dart analyze: only info-level diagnostics (no errors or warnings)
