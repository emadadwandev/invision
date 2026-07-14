class MessageItem {
  const MessageItem({
    required this.id,
    required this.subject,
    required this.body,
    required this.isGroup,
    this.senderName,
    this.senderId,
    this.recipients,
    this.createdAt,
  });

  factory MessageItem.fromJson(Map<String, dynamic> json) {
    final sender = json['sender'] as Map<String, dynamic>?;
    final recipientsList = json['recipients'] as List?;

    return MessageItem(
      id: json['id'] as int,
      subject: json['subject'] as String,
      body: json['body'] as String,
      isGroup: json['is_group'] as bool? ?? false,
      senderId: sender?['id'] as int?,
      senderName: sender?['name'] as String?,
      recipients: recipientsList
          ?.map((e) => MessageRecipientItem.fromJson(e as Map<String, dynamic>))
          .toList(),
      createdAt: json['created_at'] as String?,
    );
  }

  final int id;
  final String subject;
  final String body;
  final bool isGroup;
  final int? senderId;
  final String? senderName;
  final List<MessageRecipientItem>? recipients;
  final String? createdAt;
}

class MessageRecipientItem {
  const MessageRecipientItem({
    required this.id,
    required this.name,
    this.readAt,
    this.archivedAt,
  });

  factory MessageRecipientItem.fromJson(Map<String, dynamic> json) {
    return MessageRecipientItem(
      id: json['id'] as int,
      name: json['name'] as String,
      readAt: json['read_at'] as String?,
      archivedAt: json['archived_at'] as String?,
    );
  }

  final int id;
  final String name;
  final String? readAt;
  final String? archivedAt;
}
