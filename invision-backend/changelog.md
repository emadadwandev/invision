# Changelog — Invision SaaS Platform (Backend)

All notable changes to this project will be documented in this file.

## [Unreleased]

### Fixed
- **2026-04-05**: GPS location not appearing on command center tracking map
  - `CommandCenterService::getFieldForcePositions()` returned `latitude`/`longitude`/`speed_kmh` as PHP strings (`decimal:8` cast) instead of floats — JSON values were quoted strings, causing potential JS comparison issues
  - Fixed by casting all GPS coordinate values to `(float)` before building the response array
- **2026-04-05**: Assigned route not appearing on mobile app for field force user
  - `GET /api/v1/my-route/today` only queried `RouteInstance` rows — when admin assigns a `RoutePlan` to a user, a `RouteInstance` for today was NOT automatically created; admin had to manually call `POST /route-plans/{id}/instances`
  - Fixed: `myRouteToday()` now auto-creates a `RouteInstance` from the user's active, in-date `RoutePlan` when none exists for today; field force users see their route immediately after admin assigns the plan
- **2026-04-05**: GPS location not appearing on command center/tracking maps
  - `batchLogGps()` in `RouteService` was not dispatching `GpsPositionUpdated` broadcast event — the mobile app always sends GPS as batches (up to 10 logs), so real-time WebSocket updates never fired
  - Fixed by dispatching the event for the latest log per user after each batch insert
  - Added `User` model import to `RouteService`
  - Replaced `GpsTrackingLog::insert() ? count : 0` with a reliable count to avoid returning 0 on large batches
  - Replaced Mapbox GL JS in the command center with Leaflet + OpenStreetMap (no API token required, removes dependency on expired Mapbox token)
  - User trail ("View Trail" button) now draws a dashed blue polyline + green start marker on the Leaflet map

### Added
- **2026-04-05**: Assign Products view for stores
  - New page `GET /stores/{store}/products` to manage product assignment
  - Checkbox list of all active products with search (name/SKU) and category filter
  - Already-assigned products shown pre-checked with green "Assigned" badge
  - Select-all / deselect-all visible
  - `POST /stores/{store}/products` syncs the selection (full replace via `sync()`)
  - "Assign Products" button added to store show page header
  - "Manage" link in the Assigned Products section on show page
- **2026-04-05**: Store contacts management on create/edit views
  - Dynamic multi-contact form with Alpine.js (add/remove contacts)
  - Mark one contact as primary (radio toggle, visual ring highlight)
  - Contact type/position dropdown: Marketing, Shop Manager, Sales, Management
  - New `ContactType` enum (`App\Enums\ContactType`)
  - Validation tightened: position uses enum rule, phone required per contact
  - Edit view pre-populates existing contacts from database
- **2026-04-05**: Geography Management web views (Cities, Districts, Sectors/Zones, Areas)
  - New `GeographyController` with full CRUD for all hierarchy levels
  - Tabbed index page showing all geography levels with pagination
  - Individual create forms for each level (City, District, Sector, Area)
  - Area creation includes interactive Leaflet map picker for GPS center coordinates
  - JSON endpoints for cascading dropdowns (`/geography/countries/{id}/cities`, etc.)
  - Toggle active/inactive and delete actions in-line on index page
  - "Geography" link added to sidebar under Operations section
- **2026-04-05**: Interactive map picker on Store create and edit views
  - Replaced plain GPS latitude/longitude inputs with Leaflet.js interactive map
  - Click to place marker, drag marker to adjust position
  - Uses OpenStreetMap tiles (no API key required)
  - Coordinates auto-populate read-only lat/lng fields
  - Existing store coordinates shown on map when editing
- **2026-04-05**: Team Leader management capabilities for field force users and routes
  - Team Leaders can now create, update, and delete field force users (Promoter, Merchandiser, Field Force, Sales Representative roles)
  - Team Leaders see only mobile/field force users in the Users listing
  - Route create/edit "Assign To" dropdown scoped to field force users for Team Leaders
  - Route listing filtered to show only routes assigned to field force users for Team Leaders
  - Role dropdown in user create/edit forms restricted to mobile roles for Team Leaders
  - Server-side validation in `CreateUserRequest` and `UpdateUserRequest` prevents Team Leaders from assigning non-mobile roles

### Changed
- **2026-04-05**: Replaced top navigation bar with collapsible side navigation menu in admin portal layout (`app.blade.php`)
  - Dark sidebar (gray-900) with grouped navigation sections: Main, Management, Operations, Field, Analytics, System
  - Each nav item has a descriptive SVG icon
  - Collapsible sidebar on desktop (16rem expanded / 4.5rem icons-only collapsed)
  - Mobile: off-canvas sidebar with overlay, hamburger toggle in slim top bar
  - User profile moved to bottom of sidebar with sign-out dropdown
  - Active route highlighted with indigo-600 background
  - Smooth CSS transitions on expand/collapse

### Fixed
- **2026-04-05**: Fixed sidebar not scrollable — added `min-h-0` to nav flex child so `overflow-y-auto` works in flex column
- **2026-04-05**: Fixed sidebar appearing transparent — replaced Tailwind dynamic `:class` bindings (purged at build time) with custom CSS classes for sidebar/main-content width and padding
- **2026-04-05**: Fixed Alpine.js not loading — `echo.js` Pusher initialization crashes when `VITE_REVERB_APP_KEY` env var is missing, blocking the entire JS bundle; added guard to only initialize Echo when the key is present

### Added
- **2026-04-01**: Initial Laravel 12 project scaffold
- **2026-04-01**: Docker Compose environment (PHP 8.3-FPM, Nginx, MySQL 8, Redis, Mailpit, MinIO)
- **2026-04-01**: Laravel Sanctum installed for API token authentication
- **2026-04-01**: Phase 1 foundation — multi-tenancy, auth, RBAC, users, teams, areas
- **2026-04-01**: Added `GeographySeeder` to seed tenant-scoped cities, districts, sectors, areas, and streets for the default tenant
- **2026-04-01**: Phase 2 — Store & Product Catalog module
  - Enums: `StoreRank`, `StoreCategory`
  - Migrations: `stores`, `store_contacts`, `product_categories`, `products`, `product_price_levels`, `store_products`
  - Models: `Store`, `StoreContact`, `ProductCategory`, `Product`, `ProductPriceLevel`
  - Services: `StoreService`, `ProductService`
  - Form Requests: Store (create/update), ProductCategory (create/update), Product (create/update)
  - API Resources: `StoreResource`, `StoreContactResource`, `ProductCategoryResource`, `ProductResource`, `ProductPriceLevelResource`
  - Policies: `StorePolicy`, `ProductPolicy`
  - API Controllers: `StoreController`, `ProductController` (full CRUD + store-product assignment + toggle-active)
  - Web Controllers: `StoreController`, `ProductController` (full CRUD + product categories)
  - API Routes: stores CRUD, product-categories CRUD, products CRUD, store-product assignment
  - Web Routes: stores CRUD, products CRUD, product-categories CRUD
  - Blade Views: stores (index, create, show, edit), products (index, create, show, edit), categories (index, create, edit)
  - Sidebar navigation updated with Stores and Products links
  - Factories: `StoreFactory`, `ProductCategoryFactory`, `ProductFactory`
  - `StoreCatalogSeeder`: seeds 5 product categories with subcategories, 10 products with price levels, 5 stores with contacts and product assignments

### Fixed
- **2026-04-01**: TenantScope `apply()` and HasTenant `creating` event now check `app()->bound('current_tenant_id')` before resolving — fixes `BindingResolutionException` during CLI/seeder context
- **2026-04-01**: Added `AuthorizesRequests` trait to base `Controller` — fixes "Call to undefined method authorize()" in web controllers
- **2026-04-01**: Moved Blade layouts from `views/layouts/` to `views/components/layouts/` — fixes "Unable to locate component [layouts.guest/app]" error

## [Phase 3] - Route Management & GPS Tracking

### Added
- **2026-04-01**: Phase 3 — Route Management & GPS Tracking module
  - Enums: `RouteStatus` (Draft/Published/InProgress/Completed/Cancelled), `VisitStatus` (Pending/CheckedIn/Completed/Skipped), `VisitFrequency` (Daily/Weekly/BiWeekly/Monthly)
  - Migration: 5 new tables (`route_plans`, `route_plan_stores`, `route_instances`, `store_visits`, `gps_tracking_logs`)
  - Models: `RoutePlan`, `RoutePlanStore`, `RouteInstance`, `StoreVisit` (with Haversine distance calc), `GpsTrackingLog`
  - Service: `RouteService` — plan CRUD, store management, instance lifecycle, check-in/out with geo-fencing, GPS logging
  - Form Requests: `CreateRoutePlanRequest`, `UpdateRoutePlanRequest`, `CheckInRequest`, `CheckOutRequest`, `GpsLogRequest`
  - API Resources: `RoutePlanResource`, `RoutePlanStoreResource`, `RouteInstanceResource`, `StoreVisitResource`, `GpsTrackingLogResource`
  - Policy: `RoutePlanPolicy` (role-based authorization)
  - API Controller: `RouteController` — full plan CRUD, store add/remove/reorder, instances, check-in/out/skip, GPS log, my-routes mobile endpoints
  - Web Controller: `RouteController` — plan CRUD, instances list/show, tracking page
  - Blade Views: routes index, create, show, edit, instances, instance-show, tracking (7 views)
  - Routes registered in `web.php` and `api.php`
  - Nav bar updated with Routes and Tracking links
  - `RouteSeeder`: 3 sample route plans + sample in-progress instance with visits

## [Phase 4] - Campaigns & Marketing Materials

### Added
- **2026-04-01**: Phase 4 — Campaigns & Marketing Materials module
  - Enums: `CampaignStatus` (Draft/Scheduled/Active/Paused/Completed/Cancelled), `CampaignType` (Promotion/Discount/Sampling/Display/Posm/BuyGetFree), `TaskStatus` (Pending/InProgress/Completed/Verified/Rejected), `PosmCondition` (Good/Damaged/Missing/NeedsReplacement)
  - Migration: 9 new tables (`campaigns`, `campaign_stores`, `campaign_products`, `campaign_tasks`, `campaign_task_photos`, `campaign_entries`, `posm_materials`, `posm_placements`, `posm_check_logs`)
  - Models: `Campaign` (budget utilization, store/product targeting), `CampaignTask`, `CampaignTaskPhoto`, `CampaignEntry`, `PosmMaterial`, `PosmPlacement`, `PosmCheckLog`
  - Service: `CampaignService` — campaign CRUD with store/product targeting, task management (create/complete/verify/reject), task photos, campaign entries (QR/barcode/coupon/manual), POSM materials CRUD, POSM placements with condition check logging, mobile my-tasks endpoint
  - Form Requests: `CreateCampaignRequest`, `UpdateCampaignRequest`, `CreateCampaignTaskRequest`, `CreateCampaignEntryRequest`
  - API Resources: `CampaignResource`, `CampaignTaskResource`, `CampaignTaskPhotoResource`, `CampaignEntryResource`, `PosmMaterialResource`, `PosmPlacementResource`
  - Policy: `CampaignPolicy` (role-based authorization)
  - API Controller: `CampaignController` — full campaign CRUD, task management, entries, POSM materials/placements, my-tasks mobile endpoint
  - Web Controller: `CampaignController` — campaign CRUD, task list/show/verify/reject, POSM materials list/show
  - Blade Views: campaigns (index, create, show, edit), tasks list, task-show with verify/reject modal, materials list, material-show with placements and check logs (7 views)
  - Routes registered in `web.php` and `api.php`
  - Nav bar updated with Campaigns and POSM links (desktop + mobile)
  - `CampaignSeeder`: 3 sample campaigns (active/scheduled/draft), sample tasks, entries, 3 POSM materials, placements with condition check logs

## [Phase 5] - Sales & Collection

### Added
- **2026-04-01**: Phase 5 — Sales & Collection module
  - Enums: `OrderStatus` (Draft/Confirmed/Delivered/Cancelled/Returned), `PaymentMethod` (Cash/Check/CreditCard/BankTransfer/Credit), `PaymentStatus` (Pending/Paid/PartiallyPaid/Overdue/Refunded)
  - Migration: 7 new tables (`sales_orders`, `sales_order_items`, `payments`, `credit_accounts`, `credit_transactions`, `deposit_receipts`, `rebates`)
  - Models: `SalesOrder` (with totalPaid/balanceDue), `SalesOrderItem`, `Payment`, `CreditAccount` (with availableCredit), `CreditTransaction`, `DepositReceipt`, `Rebate` (with isCurrentlyActive)
  - Service: `SalesService` — order CRUD with auto-numbering, item management with total recalculation, payment recording with credit handling, credit account management with debit/credit transactions, deposit receipt generation, rebate CRUD with applicability matching
  - Form Requests: `CreateSalesOrderRequest`, `UpdateSalesOrderRequest`, `RecordPaymentRequest`, `CreateRebateRequest`, `UpdateRebateRequest`
  - API Resources: `SalesOrderResource`, `SalesOrderItemResource`, `PaymentResource`, `CreditAccountResource`, `DepositReceiptResource`, `RebateResource`
  - Policy: `SalesOrderPolicy` (role-based authorization)
  - API Controller: `SalesController` — full order CRUD, confirm/deliver/cancel actions, item add/remove, payments, credit accounts, deposit receipts, rebates, my-orders mobile endpoint
  - Web Controller: `SalesController` — order CRUD, payments list/record, credit accounts list/show, rebates full CRUD
  - Blade Views: orders (index, create, show, edit), payments list, credit-accounts list, credit-account-show (with utilization bar + transaction history), rebates (list, create, edit) — 10 views total
  - Routes registered in `web.php` and `api.php`
  - Nav bar updated with Sales link (desktop + mobile)
  - `SalesSeeder`: 2 rebates, 3 sample orders (delivered/confirmed/draft), payments (cash + check), 2 credit accounts with transactions

## [Phase 6] - POS Sales Operation

### Added
- **2026-04-01**: Phase 6 — POS Sales Operation module
  - Enums: `PosTransactionType` (SellOut/SellThrough/Return), `PosTransactionStatus` (Pending/Completed/Voided/Synced), `StockMovementType` (StockIn/StockOut/Adjustment/Return/SellOut/SellThrough)
  - Migration: 5 new tables (`pos_terminals`, `pos_transactions`, `pos_transaction_items`, `store_inventory`, `stock_movements`)
  - Models: `PosTerminal`, `PosTransaction`, `PosTransactionItem`, `StoreInventory` (with totalQuantity), `StockMovement` (with morphTo reference)
  - Service: `PosService` — terminal CRUD + sync, transaction CRUD with auto-numbering, complete/void/sync lifecycle, inventory tracking with adjustments, stock movement recording with quantity direction logic, mobile my-transactions
  - Form Requests: `CreatePosTerminalRequest`, `UpdatePosTerminalRequest`, `CreatePosTransactionRequest`, `UpdateInventoryRequest`, `RecordStockMovementRequest`
  - API Resources: `PosTerminalResource`, `PosTransactionResource`, `PosTransactionItemResource`, `StoreInventoryResource`, `StockMovementResource`
  - Policy: `PosTerminalPolicy` (role-based authorization)
  - API Controller: `PosController` — terminals CRUD + sync, transactions CRUD + complete/void/sync, inventory listing + per-store + update, stock movements list + record, my-transactions mobile endpoint
  - Web Controller: `PosController` — terminals CRUD, transactions CRUD + complete/void, inventory list + update, stock movements list + record
  - Blade Views: terminals (list, create, edit), transactions (list, create, show), inventory list with inline update, stock-movements list with collapsible record form — 8 views total
  - Routes registered in `web.php` and `api.php`
  - Nav bar updated with POS link (desktop + mobile)
  - `PosSeeder`: 2 POS terminals, inventory for 3 products across 2 stores, 2 sample transactions, 2 stock movements

### Fixed
- **2026-04-01**: Fixed `HasTenant` trait namespace from `App\Traits\HasTenant` to `App\Models\Concerns\HasTenant` in all POS models

## [Phase 7] - Notifications & Messaging

### Added
- **2026-04-01**: Phase 7 — Notifications & Messaging module
  - Enums: `NotificationType` (Task/Message/Alert/Announcement/System), `NotificationPriority` (Low/Normal/High/Urgent), `TaskAssignmentStatus` (Pending/InProgress/Completed/Verified/Rejected)
  - Migration: 4 new tables (`notifications`, `messages`, `message_recipients`, `task_assignments`)
  - Models: `Notification`, `Message`, `MessageRecipient`, `TaskAssignment` (with isOverdue check)
  - Service: `NotificationService` — notification CRUD with bulk send, mark-read/mark-all-read, unread count; message compose with group/individual support, inbox with archive; task assignment CRUD with complete/verify/reject lifecycle, proof of completion notes, auto-notifications on status changes
  - Form Requests: `SendNotificationRequest`, `SendMessageRequest`, `CreateTaskAssignmentRequest`, `UpdateTaskAssignmentRequest`
  - API Resources: `NotificationResource`, `MessageResource`, `TaskAssignmentResource`
  - Policy: `MessagePolicy` (sender/admin authorization for deletion)
  - API Controller: `NotificationController` — notifications CRUD, my-notifications, unread-count; messages CRUD, inbox, mark-read, archive; task assignments CRUD, complete, verify, reject, my-assigned-tasks
  - Web Controller: `NotificationController` — notifications list/create/send/delete, messages list/compose/send/show/delete, task assignments list/create/store/show/verify/reject/delete
  - Blade Views: notifications (index, create), messages (list, compose, message-show), tasks (list, task-create, task-show with verify/reject actions) — 8 views total
  - Routes registered in `web.php` and `api.php`
  - Nav bar updated with Notifications link (desktop + mobile)
  - `NotificationSeeder`: system/announcement/alert notifications, group and direct messages, 3 sample task assignments (in-progress, pending, completed)

## [Phase 8] - Command Center (Live Tracking) & Mapbox Migration

### Added
- **2026-04-01**: Phase 8 — Command Center (Live Tracking) module
  - Service: `CommandCenterService` — field force positions (latest GPS per user with online status), store map data (sales/inventory/credit summaries), store inquiry detail, user activity with GPS trail, dashboard stats (online count, active routes, today's orders/sales)
  - API Controller: `CommandCenterController` — stats, field-force positions, store map data, store inquiry, user activity endpoints
  - Web Controller: `CommandCenterController` — index view with stats/field-force/stores, fieldForcePositionsJson AJAX endpoint, store inquiry AJAX, user activity AJAX
  - Blade View: `command-center/index.blade.php` — full-screen Mapbox GL JS map, stats bar (6 cards), field force sidebar (search, online indicators, click-to-focus), store markers with popups (sales/stock/credit), user markers with online/offline status, auto-refresh positions every 30s, store inquiry slide-in panel, GPS trail drawing, Alpine.js components
  - API Routes: 5 new endpoints under `/command-center/` (stats, field-force, stores, store inquiry, user activity)
  - Web Routes: 4 new routes under `command-center.*` (index, field-force-json, store-inquiry, user-activity)
  - Nav bar updated with Command Center link (desktop + mobile)
  - `CommandCenterSeeder`: GPS tracking log entries for field force users with realistic Beirut-area coordinates, speeds, and route trails
  - Config: Mapbox token added to `config/services.php` and `.env.example`

### Changed
- **2026-04-01**: Migrated all map references from Google Maps to Mapbox
  - `tracking.blade.php`: Replaced Google Maps placeholder with Mapbox GL JS live map + sidebar user click-to-fly
  - `context.md`: Updated all Google Maps references to Mapbox throughout the project specification

## [Phase 9] - Dashboards, KPIs & Inquiry Screens

### Added
- **2026-04-01**: Phase 9 — Dashboards, KPIs & Inquiry Screens
  - Service: `DashboardService` — 8 methods: `getOverviewKpis()`, `getSalesKpis()`, `getRouteKpis()`, `getCampaignKpis()`, `getPosKpis()`, `getCreditKpis()`, `getStoreInquiry()`, `getSalesInquiry()`, `getRouteInquiry()` with period-based filtering (week/month/quarter/year)
  - API Controller: `DashboardController` — 9 endpoints: overview, sales, routes, campaigns, pos, credits, store inquiry, sales inquiry, route inquiry
  - Web Controller: `DashboardController` — dashboard index with full KPI data, 3 inquiry pages (stores, sales, routes)
  - API Routes: 6 dashboard endpoints (`/api/v1/dashboard/{overview,sales,routes,campaigns,pos,credits}`), 3 inquiry endpoints (`/api/v1/inquiry/{stores,sales,routes}`)
  - Web Routes: 3 inquiry routes (`/inquiry/{stores,sales,routes}`)
  - Dashboard Blade View: complete redesign with period selector, 5 overview KPI cards, 4 gradient today-stats cards, sales performance section with Chart.js daily trend line chart, route performance with completion bars, campaign budget utilization, POS sell-out/sell-through breakdown, credit & collections metrics, top stores and top sales reps tables, inquiry quick links
  - Inquiry Blade Views: `inquiry/stores.blade.php` (search + category/rank/area filters), `inquiry/sales.blade.php` (search + status/store/date filters), `inquiry/routes.blade.php` (status/user/date filters) — all with data tables
  - Nav bar updated with Inquiry link (desktop + mobile)

## [Phase 10] - Reporting & Export Engine

### Added
- **2026-04-01**: Phase 10 — Reporting & Export Engine
  - Packages: `phpoffice/phpspreadsheet` ^5.5 (Excel export), `barryvdh/laravel-dompdf` ^3.1 (PDF export)
  - Service: `ReportService` — 6 fixed reports (`sellThroughReport`, `sellOutReport`, `sellInReport`, `stockMovementReport`, `vendorRankingReport`, `salesRepPerformanceReport`), dynamic report builder (`buildDynamicReport`), entity metadata (`reportEntities`), Excel export (`exportExcel`), PDF export (`exportPdf`)
  - PDF Blade Template: `reports/pdf.blade.php` — styled HTML table for DomPDF rendering with landscape A4 layout
  - API Controller: `ReportController` — 10 endpoints: 6 fixed reports, entities metadata, build dynamic report (POST), export Excel, export PDF
  - Web Controller: `ReportController` — index, show, export Excel/PDF for fixed reports; builder index, run builder (POST), export builder Excel/PDF for dynamic reports
  - API Routes: 10 routes under `/api/v1/reports/` (sell-through, sell-out, sell-in, stock-movement, vendor-ranking, sales-rep-performance, entities, build, export/excel, export/pdf)
  - Web Routes: 7 routes under `/reports/` (index, {type} show with whereIn constraint, {type}/export/excel, {type}/export/pdf, builder index, builder/run, builder/export/excel, builder/export/pdf)
  - Blade Views: `reports/index.blade.php` (6 report cards in 3-col grid + builder link), `reports/show.blade.php` (date filters + data table + Excel/PDF export buttons), `reports/builder.blade.php` (Alpine.js dynamic entity selection form + results table)
  - Nav bar updated with Reports link (desktop + mobile)

### Fixed
- **2026-04-01**: Fixed `ReportService` — replaced `SUM(subtotal)` with `SUM(line_total)` in sell-through, sell-out, and sell-in reports to match actual `pos_transaction_items` and `sales_order_items` column names

## [Phase 11] - Competition Tracking & QR Code Scanning

### Added
- **2026-04-01**: Phase 11 — Competition Tracking & QR Code Scanning
  - Migration: `2026_04_01_700001_create_competition_tables` — 3 tables: `competitors` (name, description, logo_path, is_active, soft deletes), `competitor_products` (competitor_id, name, sku, barcode, category, description, image_path, is_active), `competitor_observations` (store_visit_id, store_id, user_id, competitor_id, competitor_product_id, observation_type, quantity, price, notes, photo_path, latitude, longitude, observed_at)
  - Enum: `ObservationType` — 7 cases (sales, posm, pricing, display, promotion, stock_level, other) with `label()` and `color()` methods
  - Models: `Competitor` (HasFactory, HasTenant, SoftDeletes, products/observations relations), `CompetitorProduct` (HasTenant, competitor/observations relations), `CompetitorObservation` (HasTenant, 5 BelongsTo relations)
  - Service: `CompetitorService` — full CRUD for competitors, products, observations; `getVisitObservations()`, `competitorAnalysis()` with grouped AVG(price)/SUM(quantity)
  - API Controller: `CompetitorController` — CRUD for competitors (5), products (5), observations (5), visit observations, analysis
  - Web Controller: `CompetitorController` — index, create, store, show, edit, update, destroy, products, createProduct, storeProduct, observations, analysis
  - API Routes: 11 endpoints under `/api/v1/` — competitors resource, competitor-products CRUD, competitor-observations CRUD, visit competitor observations, competitor analysis
  - Web Routes: 7 routes under `/competitors/` — resource CRUD, products (index/create/store), observations, analysis
  - Blade Views: 8 views in `pages/competitors/` — index (search/filter), show (detail + products + observations), create, edit, products (filtered list), create-product, observations (filtered list), analysis (grouped analysis cards)
  - Nav bar updated with Competitors link (desktop + mobile)
  - Seeder: `CompetitorSeeder` — 3 competitors (CompetitorX Corp, RivalBrand Inc, MarketLeader Co) with 3 products each and 5 observations each

## [Phase 12] - GPS & Geo-Fencing

### Added
- **2026-04-01**: Phase 12 — GPS Tracking, Geo-Fencing & Duty Management
  - Migration: `2026_04_01_800001_create_duty_tracking_tables` — 2 tables: `duty_sessions` (user_id, started_at/ended_at, start/end lat/lng, total_minutes, total_gps_logs, total_distance_km), `geofence_settings` (tenant-level config: checkin/checkout radius, enforce_geofence, gps_tracking_interval, batch_size, require_gps_for_checkin, auto_checkout_on_leave, auto_checkout_distance)
  - Models: `DutySession` (HasTenant, user relation, isActive()), `GeofenceSetting` (HasTenant, boolean casts)
  - Exception: `GeoFenceException` — custom exception with distance_meters and radius_meters for geo-fence violations
  - Service: `GeoFenceService` — Haversine distance calculation, geo-fence validation, settings CRUD, duty session management (start/end/active/list), trail distance calculation
  - API Controller: `GeoFenceController` — 7 endpoints: settings GET/PUT, validate POST, duty start/end/active/sessions
  - Web Controller: `GeoFenceController` — settings page, update settings, duty sessions list
  - API Routes: 7 endpoints under `/api/v1/` — geofence/settings, geofence/validate, duty/start, duty/end, duty/active, duty/sessions
  - Web Routes: 3 routes — geofence/settings (GET/PUT), geofence/duty-sessions
  - Blade Views: `pages/geofence/settings.blade.php` (configurable radius, interval, batch size, enforcement toggles), `pages/geofence/duty-sessions.blade.php` (filterable session list with user/duration/distance/status)
  - Nav bar updated with Geo-Fence link (desktop + mobile)

### Changed
- **2026-04-01**: Updated `RouteService::checkIn()` — now enforces geo-fence radius via `GeoFenceService`, throws `GeoFenceException` if user is outside configured radius or GPS is missing when required
- **2026-04-01**: Updated `RouteController::checkIn()` — catches `GeoFenceException` and returns 422 with geofence details

### Added
- **2026-04-01**: Phase 13 — WebSocket Integration (Laravel Reverb)
  - Package: `laravel/reverb` ^1.10 — WebSocket server for real-time broadcasting
  - Broadcasting config: Reverb driver, env variables (REVERB_APP_ID, REVERB_APP_KEY, REVERB_APP_SECRET, REVERB_HOST, REVERB_PORT, REVERB_SCHEME)
  - Broadcast Events:
    - `GpsPositionUpdated` — real-time GPS tracking on `private-tenant.{tenantId}.tracking` channel
    - `NotificationPushed` — real-time notifications on `private-user.{userId}.notifications` channel
    - `VisitStatusChanged` — visit check-in/check-out events on `private-tenant.{tenantId}.visits` channel
    - `DutyStatusChanged` — duty on/off events on `private-tenant.{tenantId}.duty` channel
  - Channel authorization: 4 private channels in `routes/channels.php` with tenant/user ID verification

### Changed
- **2026-04-01**: `RouteService::logGps()` — dispatches `GpsPositionUpdated` broadcast event with user position data
- **2026-04-01**: `RouteService::checkIn()` / `checkOut()` — dispatches `VisitStatusChanged` broadcast event
- **2026-04-01**: `GeoFenceService::startDuty()` / `endDuty()` — dispatches `DutyStatusChanged` broadcast event
- **2026-04-01**: `NotificationService::sendNotification()` — dispatches `NotificationPushed` broadcast event after creating notification

### Added
- **2026-04-01**: Phase 14 — Offline Support & Sync
  - Migration: `2026_04_01_900001_create_sync_tables` — `sync_tokens` (device tracking, last pull/push timestamps), `sync_queue` (offline action queue with idempotency keys, status, error handling)
  - Models: `SyncToken` (HasTenant, device tracking), `SyncQueueItem` (HasTenant, payload/status/retry management)
  - Service: `SyncService` — delta pull (entity-scoped changes since timestamp), push (process offline actions with idempotency), conflict detection, retry failed items, queue processing for GPS logs, check-ins, sales orders, competitor observations
  - API Controller: `SyncController` — 5 endpoints: pull GET, push POST, status GET, conflicts GET, retry POST
  - API Routes: 5 endpoints under `/api/v1/sync/` — pull, push, status, conflicts, retry

## [Phase 15] - Audit Trail & Multi-Factor Authentication

### Added
- **2026-04-02**: Phase 15 — Audit Trail & MFA
  - Migration: `2026_04_01_900002_create_audit_and_mfa_tables` — `audit_logs` table (tenant-scoped, user tracking, action/entity/old/new values, IP/user-agent/URL/method), indexes on tenant+entity, tenant+user, tenant+action, created_at. Conditionally adds `mfa_recovery_codes` and `mfa_confirmed_at` columns to `users` table.
  - Model: `AuditLog` — fillable, old_values/new_values cast to array, user relation
  - Service: `AuditService` — static log(), logCreated(), logUpdated() (captures dirty + original values), logDeleted(), logAuth(), query() with filters (user_id, action, entity_type, entity_id, date range)
  - Service: `MfaService` — enable() (generate 160-bit Base32 secret + 8 recovery codes), confirm() (verify TOTP), verify() (TOTP with ±1 time window tolerance or recovery code), disable(), regenerateRecoveryCodes(), custom TOTP generation (HMAC-SHA1), Base32 encode/decode, provisioning URI (otpauth://)
  - Middleware: `AuditTrailMiddleware` — auto-logs POST/PUT/PATCH/DELETE on success, extracts entity type from route name, entity ID from route params, excludes sensitive routes (auth, mfa)
  - API Controller: `AuditController` — index (paginated with filters), show
  - API Controller: `MfaController` — enable, confirm, verify, disable, status, regenerateRecoveryCodes (6 endpoints with validation)
  - Web Controller: `AuditController` — index (paginated with filters), show
  - Blade Views: `audit/index.blade.php` (filter form, paginated table with colored action badges), `audit/show.blade.php` (event details, old/new values JSON display)
  - API Routes: `audit-logs` GET/GET:id, `mfa/*` 6 endpoints (status, enable, confirm, verify, disable, recovery-codes)
  - Web Routes: `audit` GET/GET:id
  - Nav bar updated with "Audit Trail" link (desktop + mobile)
  - Registered `AuditTrailMiddleware` in API middleware group

## [Phase 16] - Multi-Language & Localization

### Added
- **2026-04-02**: Phase 16 — Multi-Language & Localization (i18n)
  - Migration: `2026_04_02_000001_add_localization_support` — adds `locale` column (default 'en') to users, creates `translation_overrides` table for tenant-scoped custom labels
  - Language files: 3 locales (en, ar, fr) with `messages.php` — 100+ translation keys covering navigation, stores, products, routes, sales, auth, statuses, audit
  - Middleware: `SetLocale` — resolves locale from query param (`?lang=`), Accept-Language header, user preference, or default. Registered in both API and web middleware groups.
  - API Controller: `LocaleController` — supported locales list, translations by locale, update user preference
  - API Routes: `GET /locales` (public), `GET /locales/{locale}/translations` (public), `PUT /locale` (authenticated)
  - User model `locale` field added to fillable

## [Phase 17] - Calendar Setup & Sales Area Management

### Added
- **2026-04-02**: Phase 17 — Calendar Setup & Sales Area Management
  - Migration: `2026_04_02_000002_create_calendar_and_sales_area_tables` — 6 tables: `calendar_weeks`, `holidays`, `calendar_events` (polymorphic), `sales_areas` (hierarchical with GeoJSON geometry), `sales_area_assignments` (user-to-area with LOB/product lines, effective dates), `sales_area_stores` (pivot)
  - Models: `CalendarWeek`, `Holiday`, `CalendarEvent` (polymorphic eventable, creator relation), `SalesArea` (self-referencing parent/children, manager, assignments, stores), `SalesAreaAssignment` (user, salesArea, product_lines JSON)
  - Service: `CalendarService` — generate business weeks, holiday CRUD, event CRUD, isHoliday() check, getWeekForDate()
  - Service: `SalesAreaService` — area CRUD with hierarchy, store assignment (sync), user assignments with effective dates, getUserAreas(), getUserStores() with cascade to child areas
  - API Controller: `CalendarController` — weeks (list, generate), holidays (CRUD + check), events (CRUD)
  - API Controller: `SalesAreaController` — areas (CRUD + hierarchy), store assignment, user assignments, my-areas, my-stores
  - API Routes: `/calendar/*` (13 endpoints), `/sales-areas/*` (11 endpoints)

### Phase 18 — Presentation Tools & Advanced Export
- Migration: `2026_04_02_000003_create_presentation_and_export_tables` — 3 tables: `report_templates` (saved custom configs, scheduling), `saved_exports` (export history with format/size), `presentation_templates` (slide definitions, themes)
- Models: `ReportTemplate`, `SavedExport`, `PresentationTemplate`
- Service: `ExportService` — CSV export (BOM-compatible), JSON export, HTML report generation (KPI grids, tables, styled sections), presentation data builder
- Service: `PresentationService` — market review generator (6 slides: title, KPIs, sales, top reps, field activity, closing), template-based generation with data source resolution, HTML conversion from presentation data, export record tracking
- API Controller: `PresentationController` — report template CRUD, generate from template, export template to Excel/PDF/CSV, presentation template CRUD, generate presentation (market_review or template-based), presentation to HTML, saved exports list/delete
- API Routes: `/presentations/*` (4 endpoints), `/report-templates/*` (9 endpoints), `/saved-exports` (2 endpoints)

### Phase 19 — Testing & Polish
- Unit Tests: `ExportServiceTest` (7 tests) — CSV BOM export, empty rows, JSON output, HTML report generation, presentation data structure, custom CSS, negative KPI class
- Unit Tests: `MfaServiceTest` (5 tests) — enable generates secret & recovery codes, verify with wrong code returns false, disable clears MFA data, regenerate recovery codes, provisioning URI format
- Feature Tests: `LocaleApiTest` (4 tests) — supported locales, translations for locale, auth requirement, update locale preference
- Feature Tests: `PresentationApiTest` (6 tests) — report template CRUD, validation, auth requirement
- Bug Fix: Added missing `mfa_enabled` column to MFA migration and User model `$fillable`/`$casts`
- Bug Fix: Fixed `AuditService::log()` — `app('current_tenant_id', null)` TypeError replaced with safe `app()->bound()` check
- All 22 PHP tests passing (75 assertions)
