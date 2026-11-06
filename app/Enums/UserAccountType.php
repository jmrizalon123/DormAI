<?php

namespace App\Enums;

enum UserAccountType: string
{
    case SuperAdmin = 'super_admin';
    case OrganizationOwner = 'organization_owner';
    case Staff = 'staff';
    case Tenant = 'tenant';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator',
            self::OrganizationOwner => 'Organization Owner',
            self::Staff => 'Staff',
            self::Tenant => 'Tenant',
        };
    }
}
