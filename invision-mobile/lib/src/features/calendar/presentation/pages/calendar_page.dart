import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../data/models/calendar_models.dart';
import '../providers/calendar_providers.dart';

final _dateFormat = DateFormat('MMM d, y');
final _timeFormat = DateFormat('h:mm a');

class CalendarPage extends ConsumerWidget {
  const CalendarPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final eventsAsync = ref.watch(calendarEventsProvider);
    final holidaysAsync = ref.watch(holidaysProvider(DateTime.now().year));

    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Calendar'),
          bottom: const TabBar(
            tabs: [
              Tab(text: 'Events'),
              Tab(text: 'Holidays'),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            // Events tab
            eventsAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Error: $e')),
              data: (events) => events.isEmpty
                  ? const Center(child: Text('No events'))
                  : ListView.builder(
                      itemCount: events.length,
                      itemBuilder: (context, i) => _EventCard(event: events[i]),
                    ),
            ),
            // Holidays tab
            holidaysAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Error: $e')),
              data: (holidays) => holidays.isEmpty
                  ? const Center(child: Text('No holidays'))
                  : ListView.builder(
                      itemCount: holidays.length,
                      itemBuilder: (context, i) =>
                          _HolidayTile(holiday: holidays[i]),
                    ),
            ),
          ],
        ),
      ),
    );
  }
}

class _EventCard extends StatelessWidget {
  const _EventCard({required this.event});
  final CalendarEventModel event;

  Color _typeColor() {
    switch (event.type) {
      case 'campaign':
        return Colors.purple;
      case 'route':
        return Colors.blue;
      case 'meeting':
        return Colors.green;
      case 'deadline':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    final color = event.color != null
        ? Color(int.parse(event.color!.replaceFirst('#', '0xFF')))
        : _typeColor();

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: color.withAlpha(30),
          child: Icon(Icons.event, color: color, size: 20),
        ),
        title: Text(event.title,
            style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              event.allDay
                  ? _dateFormat.format(event.startAt)
                  : '${_dateFormat.format(event.startAt)} ${_timeFormat.format(event.startAt)}',
              style: Theme.of(context).textTheme.bodySmall,
            ),
            Container(
              margin: const EdgeInsets.only(top: 4),
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: color.withAlpha(20),
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(event.type.toUpperCase(),
                  style: TextStyle(fontSize: 10, color: color)),
            ),
          ],
        ),
        isThreeLine: true,
      ),
    );
  }
}

class _HolidayTile extends StatelessWidget {
  const _HolidayTile({required this.holiday});
  final HolidayModel holiday;

  IconData _typeIcon() {
    switch (holiday.type) {
      case 'public':
        return Icons.public;
      case 'company':
        return Icons.business;
      case 'regional':
        return Icons.map;
      default:
        return Icons.calendar_today;
    }
  }

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: CircleAvatar(
        backgroundColor: Colors.orange.shade50,
        child: Icon(_typeIcon(), color: Colors.orange.shade700, size: 20),
      ),
      title: Text(holiday.name),
      subtitle: Text(_dateFormat.format(holiday.date)),
      trailing: holiday.isRecurring
          ? const Chip(
              label: Text('Annual', style: TextStyle(fontSize: 11)),
              visualDensity: VisualDensity.compact,
            )
          : null,
    );
  }
}
