enum StoreRank {
  platinum,
  gold,
  silver,
  bronze,
  unranked;

  String get label => switch (this) {
        platinum => 'Platinum',
        gold => 'Gold',
        silver => 'Silver',
        bronze => 'Bronze',
        unranked => 'Unranked',
      };

  static StoreRank fromString(String value) => StoreRank.values.firstWhere(
        (e) => e.name == value,
        orElse: () => StoreRank.unranked,
      );
}
