import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/presentation/pages/login_page.dart';
import '../../features/auth/presentation/providers/auth_provider.dart';
import '../../features/campaigns/presentation/pages/campaign_detail_page.dart';
import '../../features/campaigns/presentation/pages/campaign_list_page.dart';
import '../../features/campaigns/presentation/pages/my_tasks_page.dart';
import '../../features/command_center/presentation/pages/command_center_page.dart';
import '../../features/command_center/presentation/pages/user_activity_page.dart';
import '../../features/dashboard/presentation/pages/dashboard_page.dart';
import '../../features/dashboard/presentation/pages/route_inquiry_page.dart';
import '../../features/dashboard/presentation/pages/sales_inquiry_page.dart';
import '../../features/dashboard/presentation/pages/store_inquiry_page.dart';
import '../../features/notifications/presentation/pages/inbox_page.dart';
import '../../features/notifications/presentation/pages/message_detail_page.dart';
import '../../features/notifications/presentation/pages/my_assigned_tasks_page.dart';
import '../../features/notifications/presentation/pages/notifications_page.dart';
import '../../features/notifications/presentation/pages/task_assignment_detail_page.dart';
import '../../features/pos/presentation/pages/my_transactions_page.dart';
import '../../features/pos/presentation/pages/pos_transaction_detail_page.dart';
import '../../features/pos/presentation/pages/pos_transaction_list_page.dart';
import '../../features/pos/presentation/pages/store_inventory_page.dart';
import '../../features/products/presentation/pages/product_detail_page.dart';
import '../../features/products/presentation/pages/product_list_page.dart';
import '../../features/routes/presentation/pages/my_route_page.dart';
import '../../features/routes/presentation/pages/route_detail_page.dart';
import '../../features/routes/presentation/pages/route_list_page.dart';
import '../../features/sales/presentation/pages/create_order_page.dart';
import '../../features/sales/presentation/pages/my_orders_page.dart';
import '../../features/sales/presentation/pages/sales_order_detail_page.dart';
import '../../features/sales/presentation/pages/sales_order_list_page.dart';
import '../../features/reports/presentation/pages/report_builder_page.dart';
import '../../features/reports/presentation/pages/report_detail_page.dart';
import '../../features/reports/presentation/pages/reports_list_page.dart';
import '../../features/competitors/presentation/pages/competitor_list_page.dart';
import '../../features/competitors/presentation/pages/competitor_detail_page.dart';
import '../../features/competitors/presentation/pages/competitor_analysis_page.dart';
import '../../features/competitors/presentation/pages/add_observation_page.dart';
import '../../features/scanner/presentation/pages/qr_scanner_page.dart';
import '../../features/gps_tracking/presentation/pages/duty_tracking_page.dart';
import '../../features/gps_tracking/presentation/pages/geofence_check_page.dart';
import '../../features/offline/presentation/pages/sync_status_page.dart';
import '../../features/settings/presentation/pages/mfa_setup_page.dart';
import '../../features/settings/presentation/pages/language_settings_page.dart';
import '../../features/calendar/presentation/pages/calendar_page.dart';
import '../../features/calendar/presentation/pages/sales_area_list_page.dart';
import '../../features/calendar/presentation/pages/sales_area_detail_page.dart';
import '../../features/export/presentation/pages/export_dashboard_page.dart';
import '../../features/export/presentation/pages/market_review_page.dart';
import '../../features/export/presentation/pages/report_templates_page.dart';
import '../../features/export/presentation/pages/saved_exports_page.dart';
import '../../features/field_force/presentation/pages/field_force_home_page.dart';
import '../../features/stores/presentation/pages/store_detail_page.dart';
import '../../features/stores/presentation/pages/store_list_page.dart';

/// Roles that use the field-force home instead of the admin dashboard.
const _fieldForceRoles = {
  'field_force',
  'sales_representative',
  'promoter',
  'merchandiser',
};

final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);

  return GoRouter(
    initialLocation: '/login',
    redirect: (context, state) {
      final isAuthenticated = authState.isAuthenticated;
      final isLoginRoute = state.matchedLocation == '/login';
      final isFieldForceHome = state.matchedLocation == '/field-force-home';
      final isDashboard = state.matchedLocation == '/dashboard';

      if (!isAuthenticated && !isLoginRoute) return '/login';

      if (isAuthenticated && isLoginRoute) {
        final role = authState.user?.role ?? '';
        return _fieldForceRoles.contains(role) ? '/field-force-home' : '/dashboard';
      }

      // Prevent field-force users from accessing the admin dashboard directly
      if (isAuthenticated && isDashboard) {
        final role = authState.user?.role ?? '';
        if (_fieldForceRoles.contains(role)) return '/field-force-home';
      }

      // Prevent admin/team-leader users from accessing field-force home
      if (isAuthenticated && isFieldForceHome) {
        final role = authState.user?.role ?? '';
        if (!_fieldForceRoles.contains(role)) return '/dashboard';
      }

      return null;
    },
    routes: <RouteBase>[
    GoRoute(
      path: '/login',
      builder: (context, state) => const LoginPage(),
    ),
    GoRoute(
      path: '/dashboard',
      builder: (context, state) => const DashboardPage(),
    ),
    GoRoute(
      path: '/field-force-home',
      builder: (context, state) => const FieldForceHomePage(),
    ),
    GoRoute(
      path: '/stores',
      builder: (context, state) => const StoreListPage(),
    ),
    GoRoute(
      path: '/stores/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return StoreDetailPage(storeId: id);
      },
    ),
    GoRoute(
      path: '/products',
      builder: (context, state) => const ProductListPage(),
    ),
    GoRoute(
      path: '/products/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return ProductDetailPage(productId: id);
      },
    ),
    GoRoute(
      path: '/routes',
      builder: (context, state) => const RouteListPage(),
    ),
    GoRoute(
      path: '/routes/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return RouteDetailPage(routeId: id);
      },
    ),
    GoRoute(
      path: '/my-route',
      builder: (context, state) => const MyRoutePage(),
    ),
    GoRoute(
      path: '/campaigns',
      builder: (context, state) => const CampaignListPage(),
    ),
    GoRoute(
      path: '/campaigns/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return CampaignDetailPage(campaignId: id);
      },
    ),
    GoRoute(
      path: '/my-tasks',
      builder: (context, state) => const MyTasksPage(),
    ),
    GoRoute(
      path: '/sales',
      builder: (context, state) => const SalesOrderListPage(),
    ),
    GoRoute(
      path: '/sales/create',
      builder: (context, state) => const CreateOrderPage(),
    ),
    GoRoute(
      path: '/sales/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return SalesOrderDetailPage(orderId: id);
      },
    ),
    GoRoute(
      path: '/my-orders',
      builder: (context, state) => const MyOrdersPage(),
    ),
    GoRoute(
      path: '/pos',
      builder: (context, state) => const PosTransactionListPage(),
    ),
    GoRoute(
      path: '/pos/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return PosTransactionDetailPage(transactionId: id);
      },
    ),
    GoRoute(
      path: '/inventory',
      builder: (context, state) => const StoreInventoryPage(),
    ),
    GoRoute(
      path: '/my-transactions',
      builder: (context, state) => const MyTransactionsPage(),
    ),
    GoRoute(
      path: '/notifications',
      builder: (context, state) => const NotificationsPage(),
    ),
    GoRoute(
      path: '/inbox',
      builder: (context, state) => const InboxPage(),
    ),
    GoRoute(
      path: '/messages/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return MessageDetailPage(messageId: id);
      },
    ),
    GoRoute(
      path: '/assigned-tasks',
      builder: (context, state) => const MyAssignedTasksPage(),
    ),
    GoRoute(
      path: '/assigned-tasks/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return TaskAssignmentDetailPage(taskId: id);
      },
    ),
    GoRoute(
      path: '/command-center',
      builder: (context, state) => const CommandCenterPage(),
    ),
    GoRoute(
      path: '/command-center/user/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return UserActivityPage(userId: id);
      },
    ),
    GoRoute(
      path: '/inquiry/stores',
      builder: (context, state) => const StoreInquiryPage(),
    ),
    GoRoute(
      path: '/inquiry/sales',
      builder: (context, state) => const SalesInquiryPage(),
    ),
    GoRoute(
      path: '/inquiry/routes',
      builder: (context, state) => const RouteInquiryPage(),
    ),
    GoRoute(
      path: '/reports',
      builder: (context, state) => const ReportsListPage(),
    ),
    GoRoute(
      path: '/reports/builder',
      builder: (context, state) => const ReportBuilderPage(),
    ),
    GoRoute(
      path: '/reports/:slug',
      builder: (context, state) {
        final slug = state.pathParameters['slug']!;
        return ReportDetailPage(slug: slug);
      },
    ),
    // Competitors
    GoRoute(
      path: '/competitors',
      builder: (context, state) => const CompetitorListPage(),
    ),
    GoRoute(
      path: '/competitors/analysis',
      builder: (context, state) => const CompetitorAnalysisPage(),
    ),
    GoRoute(
      path: '/competitors/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return CompetitorDetailPage(competitorId: id);
      },
    ),
    GoRoute(
      path: '/competitors/observe/:storeId',
      builder: (context, state) {
        final storeId = int.parse(state.pathParameters['storeId']!);
        final visitId = state.uri.queryParameters['visitId'];
        return AddObservationPage(
          storeId: storeId,
          storeVisitId: visitId != null ? int.tryParse(visitId) : null,
        );
      },
    ),
    // QR Scanner
    GoRoute(
      path: '/scanner',
      builder: (context, state) => const QrScannerPage(),
    ),
    // Duty Tracking
    GoRoute(
      path: '/duty',
      builder: (context, state) => const DutyTrackingPage(),
    ),
    // Geo-Fence Check
    GoRoute(
      path: '/geofence-check',
      builder: (context, state) {
        final extra = state.extra as Map<String, dynamic>;
        return GeoFenceCheckPage(
          storeName: extra['store_name'] as String,
          storeLat: extra['store_lat'] as double,
          storeLng: extra['store_lng'] as double,
        );
      },
    ),
    // Sync Status
    GoRoute(
      path: '/sync',
      builder: (context, state) => const SyncStatusPage(),
    ),
    // MFA Settings
    GoRoute(
      path: '/mfa-setup',
      builder: (context, state) => const MfaSetupPage(),
    ),
    // Language Settings
    GoRoute(
      path: '/language',
      builder: (context, state) => const LanguageSettingsPage(),
    ),
    // Calendar
    GoRoute(
      path: '/calendar',
      builder: (context, state) => const CalendarPage(),
    ),
    // Sales Areas
    GoRoute(
      path: '/sales-areas',
      builder: (context, state) => const SalesAreaListPage(),
    ),
    GoRoute(
      path: '/sales-areas/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return SalesAreaDetailPage(areaId: id);
      },
    ),
    // Export & Presentations
    GoRoute(
      path: '/exports',
      builder: (context, state) => const ExportDashboardPage(),
    ),
    GoRoute(
      path: '/presentations/market-review',
      builder: (context, state) => const MarketReviewPage(),
    ),
    GoRoute(
      path: '/report-templates',
      builder: (context, state) => const ReportTemplatesPage(),
    ),
    GoRoute(
      path: '/saved-exports',
      builder: (context, state) => const SavedExportsPage(),
    ),
  ],
  );
});
