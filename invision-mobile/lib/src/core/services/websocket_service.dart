import 'dart:async';
import 'dart:convert';

import 'package:web_socket_channel/web_socket_channel.dart';

/// Configuration for connecting to Laravel Reverb WebSocket server.
class ReverbConfig {
  const ReverbConfig({
    required this.host,
    required this.port,
    required this.appKey,
    this.scheme = 'ws',
  });

  final String host;
  final int port;
  final String appKey;
  final String scheme;

  Uri get wsUri => Uri.parse('$scheme://$host:$port/app/$appKey');
}

/// Event received from a WebSocket channel.
class ReverbEvent {
  const ReverbEvent({
    required this.event,
    required this.channel,
    required this.data,
  });

  final String event;
  final String channel;
  final Map<String, dynamic> data;
}

typedef EventCallback = void Function(ReverbEvent event);

/// Service that manages a persistent WebSocket connection to Laravel Reverb.
///
/// Supports subscribing to private channels and listening for broadcast events.
class WebSocketService {
  WebSocketService({required this.config, this.authToken});

  final ReverbConfig config;
  String? authToken;

  WebSocketChannel? _channel;
  StreamSubscription<dynamic>? _subscription;
  String? _socketId;
  bool _connected = false;
  Timer? _pingTimer;

  final _subscribedChannels = <String>{};
  final _eventListeners = <String, List<EventCallback>>{};
  final _connectionController = StreamController<bool>.broadcast();

  /// Stream that emits connection state changes.
  Stream<bool> get connectionStream => _connectionController.stream;
  bool get isConnected => _connected;
  String? get socketId => _socketId;

  /// Connect to the Reverb WebSocket server.
  void connect() {
    if (_connected) return;

    try {
      _channel = WebSocketChannel.connect(config.wsUri);

      _subscription = _channel!.stream.listen(
        _onMessage,
        onError: _onError,
        onDone: _onDone,
      );

      // Start ping timer to keep connection alive
      _pingTimer = Timer.periodic(
        const Duration(seconds: 30),
        (_) => _sendPing(),
      );
    } catch (e) {
      _scheduleReconnect();
    }
  }

  /// Disconnect from the WebSocket server.
  void disconnect() {
    _pingTimer?.cancel();
    _pingTimer = null;
    _subscription?.cancel();
    _subscription = null;
    _channel?.sink.close();
    _channel = null;
    _connected = false;
    _socketId = null;
    _subscribedChannels.clear();
    _connectionController.add(false);
  }

  /// Update the auth token (e.g., after login).
  void setAuthToken(String token) {
    authToken = token;
  }

  /// Subscribe to a private channel.
  void subscribePrivate(String channelName) {
    final fullChannel = channelName.startsWith('private-')
        ? channelName
        : 'private-$channelName';

    if (_subscribedChannels.contains(fullChannel)) return;
    _subscribedChannels.add(fullChannel);

    if (_connected && _socketId != null) {
      _sendSubscribe(fullChannel);
    }
  }

  /// Unsubscribe from a channel.
  void unsubscribe(String channelName) {
    final fullChannel = channelName.startsWith('private-')
        ? channelName
        : 'private-$channelName';

    _subscribedChannels.remove(fullChannel);

    if (_connected) {
      _send({
        'event': 'pusher:unsubscribe',
        'data': {'channel': fullChannel},
      });
    }

    // Remove listeners for this channel
    _eventListeners.removeWhere(
      (key, _) => key.startsWith('$fullChannel:'),
    );
  }

  /// Listen for a specific event on a channel.
  void on(String channelName, String eventName, EventCallback callback) {
    final fullChannel = channelName.startsWith('private-')
        ? channelName
        : 'private-$channelName';
    final key = '$fullChannel:$eventName';

    _eventListeners.putIfAbsent(key, () => []).add(callback);
  }

  /// Remove a listener.
  void off(String channelName, String eventName, EventCallback callback) {
    final fullChannel = channelName.startsWith('private-')
        ? channelName
        : 'private-$channelName';
    final key = '$fullChannel:$eventName';

    _eventListeners[key]?.remove(callback);
  }

  void _onMessage(dynamic raw) {
    try {
      final message = jsonDecode(raw as String) as Map<String, dynamic>;
      final event = message['event'] as String?;

      switch (event) {
        case 'pusher:connection_established':
          final data =
              jsonDecode(message['data'] as String) as Map<String, dynamic>;
          _socketId = data['socket_id'] as String?;
          _connected = true;
          _connectionController.add(true);

          // Re-subscribe to all channels
          for (final ch in _subscribedChannels) {
            _sendSubscribe(ch);
          }

        case 'pusher:error':
          // Connection error from server
          break;

        case 'pusher_internal:subscription_succeeded':
          // Channel subscription confirmed
          break;

        case 'pusher:pong':
          // Pong received — connection alive
          break;

        default:
          // Broadcast event
          if (event != null && message.containsKey('channel')) {
            final channel = message['channel'] as String;
            final dynamic rawData = message['data'];
            final Map<String, dynamic> eventData;

            if (rawData is String) {
              eventData = jsonDecode(rawData) as Map<String, dynamic>;
            } else if (rawData is Map<String, dynamic>) {
              eventData = rawData;
            } else {
              eventData = {};
            }

            final reverbEvent = ReverbEvent(
              event: event,
              channel: channel,
              data: eventData,
            );

            final key = '$channel:$event';
            final listeners = _eventListeners[key];
            if (listeners != null) {
              for (final cb in listeners) {
                cb(reverbEvent);
              }
            }
          }
      }
    } catch (_) {
      // Ignore malformed messages
    }
  }

  void _onError(Object error) {
    _connected = false;
    _connectionController.add(false);
    _scheduleReconnect();
  }

  void _onDone() {
    _connected = false;
    _connectionController.add(false);
    _scheduleReconnect();
  }

  void _sendSubscribe(String channel) {
    if (channel.startsWith('private-')) {
      // Private channels require auth — for now include the bearer token
      // In production, this should hit a /broadcasting/auth endpoint
      _send({
        'event': 'pusher:subscribe',
        'data': {
          'channel': channel,
          'auth': authToken ?? '',
        },
      });
    } else {
      _send({
        'event': 'pusher:subscribe',
        'data': {'channel': channel},
      });
    }
  }

  void _sendPing() {
    if (_connected) {
      _send({'event': 'pusher:ping', 'data': {}});
    }
  }

  void _send(Map<String, dynamic> data) {
    if (_channel != null) {
      _channel!.sink.add(jsonEncode(data));
    }
  }

  Timer? _reconnectTimer;

  void _scheduleReconnect() {
    _reconnectTimer?.cancel();
    _reconnectTimer = Timer(const Duration(seconds: 5), () {
      disconnect();
      connect();
    });
  }

  /// Dispose resources.
  void dispose() {
    _reconnectTimer?.cancel();
    disconnect();
    _connectionController.close();
  }
}
