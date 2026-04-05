<?php

namespace App\Enums;

enum NotificationType: string
{
    case Task = 'task';
    case Message = 'message';
    case Alert = 'alert';
    case Announcement = 'announcement';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Task => 'Task',
            self::Message => 'Message',
            self::Alert => 'Alert',
            self::Announcement => 'Announcement',
            self::System => 'System',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Task => 'blue',
            self::Message => 'green',
            self::Alert => 'red',
            self::Announcement => 'purple',
            self::System => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Task => 'clipboard-check',
            self::Message => 'chat-bubble-left',
            self::Alert => 'exclamation-triangle',
            self::Announcement => 'megaphone',
            self::System => 'cog',
        };
    }
}
