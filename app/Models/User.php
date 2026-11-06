<?php

namespace App\Models;

use App\Enums\UserAccountType;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'password',
        'account_type',
        'status',
        'phone',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'account_type' => UserAccountType::class,
            'status' => UserStatus::class,
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function loginActivities(): HasMany
    {
        return $this->hasMany(LoginActivity::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->account_type === UserAccountType::SuperAdmin;
    }

    public function isOrganizationOwner(): bool
    {
        return $this->account_type === UserAccountType::OrganizationOwner;
    }

    public function isTenant(): bool
    {
        return $this->account_type === UserAccountType::Tenant;
    }

    public function tenantProfile(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }
}
