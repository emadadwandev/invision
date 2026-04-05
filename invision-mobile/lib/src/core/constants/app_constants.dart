class AppConstants {
  const AppConstants._();

  static const String appName = 'Invision Mobile';
  static const String apiBaseUrl = 'http://192.168.1.32:8000/api/v1';
}

class ApiEndpoints {
  const ApiEndpoints._();

  // Auth
  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String me = '/auth/me';

  // Stores
  static const String stores = '/stores';
  static String store(int id) => '/stores/$id';
  static String toggleStoreActive(int id) => '/stores/$id/toggle-active';
  static String storeProducts(int id) => '/stores/$id/products';

  // Products
  static const String products = '/products';
  static String product(int id) => '/products/$id';

  // Product Categories
  static const String productCategories = '/product-categories';
  static const String productCategoryTree = '/product-categories/tree';
  static String productCategory(int id) => '/product-categories/$id';

  // Route Plans
  static const String routePlans = '/route-plans';
  static String routePlan(int id) => '/route-plans/$id';

  // Route Instances
  static const String routeInstances = '/route-instances';
  static String routeInstance(int id) => '/route-instances/$id';
  static String startRouteInstance(int id) => '/route-instances/$id/start';
  static String completeRouteInstance(int id) =>
      '/route-instances/$id/complete';
  static String createRouteInstance(int planId) =>
      '/route-plans/$planId/instances';

  // My Routes (Mobile)
  static const String myRouteToday = '/my-route/today';
  static const String myRoutes = '/my-routes';

  // Store Visits
  static String visitCheckIn(int id) => '/visits/$id/check-in';
  static String visitCheckOut(int id) => '/visits/$id/check-out';
  static String visitSkip(int id) => '/visits/$id/skip';

  // GPS Tracking
  static const String gpsLog = '/gps/log';
  static const String gpsTracking = '/gps/tracking';

  // Campaigns
  static const String campaigns = '/campaigns';
  static String campaign(int id) => '/campaigns/$id';
  static String campaignTasks(int id) => '/campaigns/$id/tasks';
  static String campaignEntries(int id) => '/campaigns/$id/entries';

  // Campaign Tasks
  static const String campaignTasksStore = '/campaign-tasks';
  static String campaignTask(int id) => '/campaign-tasks/$id';
  static String completeTask(int id) => '/campaign-tasks/$id/complete';
  static String verifyTask(int id) => '/campaign-tasks/$id/verify';
  static String rejectTask(int id) => '/campaign-tasks/$id/reject';
  static String uploadTaskPhoto(int id) => '/campaign-tasks/$id/photos';

  // Campaign Entries
  static const String campaignEntriesStore = '/campaign-entries';

  // POSM
  static const String posmMaterials = '/posm-materials';
  static String posmMaterial(int id) => '/posm-materials/$id';
  static const String posmPlacements = '/posm-placements';
  static String checkPlacement(int id) => '/posm-placements/$id/check';

  // My Tasks (Mobile)
  static const String myTasks = '/my-tasks';

  // Sales Orders
  static const String salesOrders = '/sales-orders';
  static String salesOrder(int id) => '/sales-orders/$id';
  static String confirmOrder(int id) => '/sales-orders/$id/confirm';
  static String deliverOrder(int id) => '/sales-orders/$id/deliver';
  static String cancelOrder(int id) => '/sales-orders/$id/cancel';
  static String addOrderItem(int id) => '/sales-orders/$id/items';
  static String removeOrderItem(int orderId, int itemId) =>
      '/sales-orders/$orderId/items/$itemId';

  // Payments
  static const String payments = '/payments';
  static String recordPayment(int id) => '/payments/$id';

  // Credit Accounts
  static const String creditAccounts = '/credit-accounts';
  static String creditAccount(int id) => '/credit-accounts/$id';
  static String creditPayment(int id) => '/credit-accounts/$id/credit-payments';

  // Deposit Receipts
  static const String depositReceipts = '/deposit-receipts';

  // Rebates
  static const String rebates = '/rebates';
  static String rebate(int id) => '/rebates/$id';
  static const String applicableRebates = '/applicable-rebates';

  // My Orders (Mobile)
  static const String myOrders = '/my-orders';

  // POS Terminals
  static const String posTerminals = '/pos-terminals';
  static String posTerminal(int id) => '/pos-terminals/$id';
  static String syncTerminal(int id) => '/pos-terminals/$id/sync';

  // POS Transactions
  static const String posTransactions = '/pos-transactions';
  static String posTransaction(int id) => '/pos-transactions/$id';
  static String completeTransaction(int id) => '/pos-transactions/$id/complete';
  static String voidTransaction(int id) => '/pos-transactions/$id/void';
  static String syncTransaction(int id) => '/pos-transactions/$id/sync';

  // Store Inventory
  static const String storeInventory = '/store-inventory';
  static String storeInventoryByStore(int storeId) =>
      '/store-inventory/$storeId';
  static String updateInventory(int id) => '/store-inventory/$id';

  // Stock Movements
  static const String stockMovements = '/stock-movements';

  // My Transactions (Mobile POS)
  static const String myTransactions = '/my-transactions';

  // Notifications
  static const String notifications = '/notifications';
  static String notification(int id) => '/notifications/$id';
  static String markNotificationRead(int id) => '/notifications/$id/read';
  static const String markAllNotificationsRead = '/notifications/mark-all-read';
  static const String unreadNotificationCount = '/notifications/unread-count';
  static const String myNotifications = '/my-notifications';

  // Messages
  static const String messages = '/messages';
  static const String inbox = '/inbox';
  static String message(int id) => '/messages/$id';
  static String markMessageRead(int id) => '/messages/$id/read';
  static String archiveMessage(int id) => '/messages/$id/archive';

  // Task Assignments
  static const String taskAssignments = '/task-assignments';
  static String taskAssignment(int id) => '/task-assignments/$id';
  static String completeTaskAssignment(int id) => '/task-assignments/$id/complete';
  static String verifyTaskAssignment(int id) => '/task-assignments/$id/verify';
  static String rejectTaskAssignment(int id) => '/task-assignments/$id/reject';
  static const String myAssignedTasks = '/my-assigned-tasks';

  // Command Center (Live Tracking)
  static const String commandCenterStats = '/command-center/stats';
  static const String commandCenterFieldForce = '/command-center/field-force';
  static const String commandCenterStores = '/command-center/stores';
  static String commandCenterStoreInquiry(int id) =>
      '/command-center/stores/$id/inquiry';
  static String commandCenterUserActivity(int id) =>
      '/command-center/users/$id/activity';

  // Dashboard & KPIs
  static const String dashboardOverview = '/dashboard/overview';
  static const String dashboardSales = '/dashboard/sales';
  static const String dashboardRoutes = '/dashboard/routes';
  static const String dashboardCampaigns = '/dashboard/campaigns';
  static const String dashboardPos = '/dashboard/pos';
  static const String dashboardCredits = '/dashboard/credits';

  // Inquiry Screens
  static const String inquiryStores = '/inquiry/stores';
  static const String inquirySales = '/inquiry/sales';
  static const String inquiryRoutes = '/inquiry/routes';

  // Reports
  static const String reportSellThrough = '/reports/sell-through';
  static const String reportSellOut = '/reports/sell-out';
  static const String reportSellIn = '/reports/sell-in';
  static const String reportStockMovement = '/reports/stock-movement';
  static const String reportVendorRanking = '/reports/vendor-ranking';
  static const String reportSalesRepPerformance = '/reports/sales-rep-performance';
  static const String reportEntities = '/reports/entities';
  static const String reportBuild = '/reports/build';
  static const String reportExportExcel = '/reports/export/excel';
  static const String reportExportPdf = '/reports/export/pdf';

  // Competitors
  static const String competitors = '/competitors';
  static String competitor(int id) => '/competitors/$id';

  // Competitor Products
  static const String competitorProducts = '/competitor-products';
  static String competitorProduct(int id) => '/competitor-products/$id';

  // Competitor Observations
  static const String competitorObservations = '/competitor-observations';
  static String competitorObservation(int id) =>
      '/competitor-observations/$id';
  static String visitCompetitorObservations(int visitId) =>
      '/visits/$visitId/competitor-observations';

  // Competitor Analysis
  static const String competitorAnalysis = '/competitor-analysis';

  // Geo-Fence & Duty
  static const String geofenceSettings = '/geofence/settings';
  static const String geofenceValidate = '/geofence/validate';
  static const String dutyStart = '/duty/start';
  static const String dutyEnd = '/duty/end';
  static const String dutyActive = '/duty/active';
  static const String dutySessions = '/duty/sessions';

  // Sync (Offline Support)
  static const String syncPull = '/sync/pull';
  static const String syncPush = '/sync/push';
  static const String syncStatus = '/sync/status';
  static const String syncConflicts = '/sync/conflicts';
  static const String syncRetry = '/sync/retry';

  // MFA (Multi-Factor Authentication)
  static const String mfaStatus = '/mfa/status';
  static const String mfaEnable = '/mfa/enable';
  static const String mfaConfirm = '/mfa/confirm';
  static const String mfaVerify = '/mfa/verify';
  static const String mfaDisable = '/mfa/disable';
  static const String mfaRecoveryCodes = '/mfa/recovery-codes';

  // Locale & i18n
  static const String locales = '/locales';
  static String localeTranslations(String locale) => '/locales/$locale/translations';
  static const String localePreference = '/locale';

  // Calendar
  static const String calendarWeeks = '/calendar/weeks';
  static const String calendarWeeksGenerate = '/calendar/weeks/generate';
  static const String calendarHolidays = '/calendar/holidays';
  static const String calendarHolidayCheck = '/calendar/holidays/check';
  static const String calendarEvents = '/calendar/events';
  static String calendarEvent(int id) => '/calendar/events/$id';

  // Sales Areas
  static const String salesAreas = '/sales-areas';
  static const String salesAreasHierarchy = '/sales-areas/hierarchy';
  static const String myAreas = '/sales-areas/my-areas';
  static const String myAreaStores = '/sales-areas/my-stores';
  static String salesArea(int id) => '/sales-areas/$id';
  static String salesAreaStores(int id) => '/sales-areas/$id/stores';
  static String salesAreaAssignments(int id) => '/sales-areas/$id/assignments';

  // Presentation & Export
  static const String presentationTemplates = '/presentations/templates';
  static const String presentationGenerate = '/presentations/generate';
  static const String presentationGenerateHtml = '/presentations/generate-html';
  static const String reportTemplates = '/report-templates';
  static String reportTemplate(int id) => '/report-templates/$id';
  static String reportTemplateGenerate(int id) => '/report-templates/$id/generate';
  static String reportTemplateExportExcel(int id) => '/report-templates/$id/export/excel';
  static String reportTemplateExportPdf(int id) => '/report-templates/$id/export/pdf';
  static String reportTemplateExportCsv(int id) => '/report-templates/$id/export/csv';
  static const String savedExports = '/saved-exports';
  static String savedExport(int id) => '/saved-exports/$id';
}
