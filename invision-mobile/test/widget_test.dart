import 'package:flutter_test/flutter_test.dart';

import 'package:invision_mobile/src/app.dart';

void main() {
  testWidgets('Shows login screen on app start', (WidgetTester tester) async {
    await tester.pumpWidget(const InvisionApp());
    await tester.pumpAndSettle();

    expect(find.text('Sign In'), findsOneWidget);
    expect(find.text('Login'), findsOneWidget);
  });
}
