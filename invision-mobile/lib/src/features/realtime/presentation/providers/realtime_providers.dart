import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/services/websocket_service.dart';

// ---------------------------------------------------------------------------
// Configuration — in production, load from env / remote config
// ---------------------------------------------------------------------------

const _defaultConfig = ReverbConfig(
  host: '10.0.2.2', // Android emulator → host machine
  port: 8080,
  appKey: 'invision-reverb-key',
);

// ---------------------------------------------------------------------------
// Core providers
// ---------------------------------------------------------------------------

/// Singleton WebSocket service managed by Riverpod.
final webSocketServiceProvider = Provider<WebSocketService>((ref) {
  final service = WebSocketService(config: _defaultConfig);
  ref.onDispose(service.dispose);
  return service;
});

/// Stream of connection state (true = connected).
final wsConnectionProvider = StreamProvider<bool>((ref) {
  final service = ref.watch(webSocketServiceProvider);
  return service.connectionStream;
});

// ---------------------------------------------------------------------------
// Channel subscription helpers
// ---------------------------------------------------------------------------

/// Subscribe to tenant-level tracking channel.
/// Returns a stream of GPS position updates.
final liveGpsTrackingProvider =
    StreamProvider.family<ReverbEvent, int>((ref, tenantId) {
  final service = ref.watch(webSocketServiceProvider);
  final controller = StreamController<ReverbEvent>();

  final channel = 'tenant.$tenantId.tracking';
  service.subscribePrivate(channel);

  void onEvent(ReverbEvent event) {
    controller.add(event);
  }

  service.on(channel, 'gps.position.updated', onEvent);

  ref.onDispose(() {
    service.off(channel, 'gps.position.updated', onEvent);
    service.unsubscribe(channel);
    controller.close();
  });

  return controller.stream;
});

/// Subscribe to user-level notification channel.
/// Returns a stream of pushed notifications.
final liveNotificationProvider =
    StreamProvider.family<ReverbEvent, int>((ref, userId) {
  final service = ref.watch(webSocketServiceProvider);
  final controller = StreamController<ReverbEvent>();

  final channel = 'user.$userId.notifications';
  service.subscribePrivate(channel);

  void onEvent(ReverbEvent event) {
    controller.add(event);
  }

  service.on(channel, 'notification.pushed', onEvent);

  ref.onDispose(() {
    service.off(channel, 'notification.pushed', onEvent);
    service.unsubscribe(channel);
    controller.close();
  });

  return controller.stream;
});

/// Subscribe to tenant-level visit status channel.
/// Returns a stream of visit check-in / check-out events.
final liveVisitStatusProvider =
    StreamProvider.family<ReverbEvent, int>((ref, tenantId) {
  final service = ref.watch(webSocketServiceProvider);
  final controller = StreamController<ReverbEvent>();

  final channel = 'tenant.$tenantId.visits';
  service.subscribePrivate(channel);

  void onEvent(ReverbEvent event) {
    controller.add(event);
  }

  service.on(channel, 'visit.status.changed', onEvent);

  ref.onDispose(() {
    service.off(channel, 'visit.status.changed', onEvent);
    service.unsubscribe(channel);
    controller.close();
  });

  return controller.stream;
});

/// Subscribe to tenant-level duty status channel.
/// Returns a stream of duty on/off events.
final liveDutyStatusProvider =
    StreamProvider.family<ReverbEvent, int>((ref, tenantId) {
  final service = ref.watch(webSocketServiceProvider);
  final controller = StreamController<ReverbEvent>();

  final channel = 'tenant.$tenantId.duty';
  service.subscribePrivate(channel);

  void onEvent(ReverbEvent event) {
    controller.add(event);
  }

  service.on(channel, 'duty.status.changed', onEvent);

  ref.onDispose(() {
    service.off(channel, 'duty.status.changed', onEvent);
    service.unsubscribe(channel);
    controller.close();
  });

  return controller.stream;
});
