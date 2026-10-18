import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

enum StockMovementType {
  stockIn,
  stockOut,
  adjustment,
  stockReturn,
  sellOut,
  sellThrough;

  static StockMovementType fromString(String value) {
    return switch (value) {
      'stock_in' => StockMovementType.stockIn,
      'stock_out' => StockMovementType.stockOut,
      'adjustment' => StockMovementType.adjustment,
      'return' => StockMovementType.stockReturn,
      'sell_out' => StockMovementType.sellOut,
      'sell_through' => StockMovementType.sellThrough,
      _ => StockMovementType.stockIn,
    };
  }

  String get value => switch (this) {
    StockMovementType.stockIn => 'stock_in',
    StockMovementType.stockOut => 'stock_out',
    StockMovementType.adjustment => 'adjustment',
    StockMovementType.stockReturn => 'return',
    StockMovementType.sellOut => 'sell_out',
    StockMovementType.sellThrough => 'sell_through',
  };

  String get label => switch (this) {
    StockMovementType.stockIn => 'Stock In',
    StockMovementType.stockOut => 'Stock Out',
    StockMovementType.adjustment => 'Adjustment',
    StockMovementType.stockReturn => 'Return',
    StockMovementType.sellOut => 'Sell Out',
    StockMovementType.sellThrough => 'Sell Through',
  };

  Color get color => switch (this) {
    StockMovementType.stockIn => AppColors.secondary,
    StockMovementType.stockOut => AppColors.error,
    StockMovementType.adjustment => AppColors.tertiary,
    StockMovementType.stockReturn => AppColors.primaryContainer,
    StockMovementType.sellOut => AppColors.primary,
    StockMovementType.sellThrough => AppColors.secondary,
  };
}
