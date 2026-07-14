import 'package:flutter/material.dart';

enum PosTransactionStatus {
  pending,
  completed,
  voided,
  synced;

  static PosTransactionStatus fromString(String value) {
    return switch (value) {
      'pending' => PosTransactionStatus.pending,
      'completed' => PosTransactionStatus.completed,
      'voided' => PosTransactionStatus.voided,
      'synced' => PosTransactionStatus.synced,
      _ => PosTransactionStatus.pending,
    };
  }

  String get value => switch (this) {
    PosTransactionStatus.pending => 'pending',
    PosTransactionStatus.completed => 'completed',
    PosTransactionStatus.voided => 'voided',
    PosTransactionStatus.synced => 'synced',
  };

  String get label => switch (this) {
    PosTransactionStatus.pending => 'Pending',
    PosTransactionStatus.completed => 'Completed',
    PosTransactionStatus.voided => 'Voided',
    PosTransactionStatus.synced => 'Synced',
  };

  Color get color => switch (this) {
    PosTransactionStatus.pending => Colors.orange,
    PosTransactionStatus.completed => Colors.green,
    PosTransactionStatus.voided => Colors.red,
    PosTransactionStatus.synced => Colors.blue,
  };
}
