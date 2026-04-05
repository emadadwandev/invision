<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Administrator = 'administrator';
    case TeamLeader = 'team_leader';
    case AccountManager = 'account_manager';
    case Promoter = 'promoter';
    case Merchandiser = 'merchandiser';
    case FieldForce = 'field_force';
    case SalesRepresentative = 'sales_representative';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Administrator => 'Administrator',
            self::TeamLeader => 'Team Leader',
            self::AccountManager => 'Account Manager',
            self::Promoter => 'Promoter',
            self::Merchandiser => 'Merchandiser',
            self::FieldForce => 'Field Force',
            self::SalesRepresentative => 'Sales Representative',
        };
    }

    public function isBackendUser(): bool
    {
        return in_array($this, [
            self::SuperAdmin,
            self::Administrator,
            self::TeamLeader,
            self::AccountManager,
        ]);
    }

    public function isMobileUser(): bool
    {
        return in_array($this, [
            self::Promoter,
            self::Merchandiser,
            self::FieldForce,
            self::SalesRepresentative,
        ]);
    }
}
