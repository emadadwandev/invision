import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../../core/theme/app_theme.dart';
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
        backgroundColor: AppColors.background,
        appBar: AppBar(
          title: Text('Calendar',
              style: Theme.of(context).textTheme.headlineMedium
                  ?.copyWith(color: AppColors.onSurface)),
          backgroundColor: AppColors.surface.withOpacity(0.9),
          elevation: 0, scrolledUnderElevation: 0,
          bottom: const TabBar(
            labelColor: AppColors.primary,
            unselectedLabelColor: AppColors.onSurfaceVariant,
            indicatorColor: AppColors.primary,
            tabs: [
              Tab(text: 'Events'),
              Tab(text: 'Holidays'),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            eventsAsync.when(
              loading: () => const Center(
                  child: CircularProgressIndicator(color: AppColors.primary)),
              error: (e, _) => Center(child: Text('Error: $e')),
              data: (events) => events.isEmpty
                  ? const Center(
                      child: Text('No events',
                          style: TextStyle(color: AppColors.onSurfaceVariant)))
                  : ListView.builder(
                      padding: const EdgeInsets.all(12),
                      itemCount: events.length,
                      itemBuilder: (context, i) =>
                          _EventCard(event: events[i]),
                    ),
            ),
            holidaysAsync.when(
              loading: () => const Center(
                  child: CircularProgressIndicator(color: AppColors.primary)),
              error: (e, _) => Center(child: Text('Error: $e')),
              data: (holidays) => holidays.isEmpty
                  ? const Center(
                      child: Text('No holidays',
                          style: TextStyle(color: AppColors.onSurfaceVariant)))
                  : ListView.builder(
                      padding: const EdgeInsets.all(12),
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
      case 'campaign': return AppColors.primary;
      case 'route': return AppColors.primaryContainer;
      case 'meeting': return AppColors.secondary;
      case 'deadline': return AppColors.error;
      default: return AppColors.outline;
    }
  }

  @override
  Widget build(BuildContext context) {
    final color = event.color != null
        ? Color(int.parse(event.color!.replaceFirst('#', '0xFF')))
        : _typeColor();

    return Container(
      margin: const EdgeInsets.symmetric(vertical: 4),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(14),
        border: Border(left: BorderSide(color: color, width: 3)),
      ),
      child: Row(
        children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(Icons.event_rounded, color: color, size: 18),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(event.title,
                    style: const TextStyle(
                        fontWeight: FontWeight.w700, color: AppColors.onSurface)),
                const SizedBox(height: 2),
                Text(
                  event.allDay
                      ? _dateFormat.format(event.startAt)
                      : '${_dateFormat.format(event.startAt)} ${_timeFormat.format(event.startAt)}',
                  style: const TextStyle(
                      fontSize: 12, color: AppColors.onSurfaceVariant),
                ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(100),
                  ),
                  child: Text(event.type.toUpperCase(),
                      style: TextStyle(
                          fontSize: 10, color: color, fontWeight: FontWeight.w700)),
                ),
              ],
            ),
          ),
        ],
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
    return Container(
      margin: const EdgeInsets.symmetric(vertical: 4),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
      ),
      child: Row(
        children: [
          Container(
            width: 36, height: 36,
            decoration: BoxDecoration(
              color: AppColors.tertiary.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(_typeIcon(), color: AppColors.tertiary, size: 18),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(holiday.name,
                    style: const TextStyle(
                        fontWeight: FontWeight.w700, color: AppColors.onSurface)),
                Text(_dateFormat.format(holiday.date),
                    style: const TextStyle(
                        fontSize: 12, color: AppColors.onSurfaceVariant)),
              ],
            ),
          ),
          if (holiday.isRecurring)
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerHigh,
                borderRadius: BorderRadius.circular(100),
              ),
              child: const Text('Annual',
                  style: TextStyle(
                      fontSize: 10,
                      color: AppColors.onSurfaceVariant,
                      fontWeight: FontWeight.w600)),
            ),
        ],
      ),
    );
  }
}
