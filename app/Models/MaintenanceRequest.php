<?php

namespace App\Models;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\WorkOrderStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MaintenanceRequest extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'reference_code',
        'tenant_id',
        'property_id',
        'room_id',
        'reported_by',
        'title',
        'description',
        'priority',
        'status',
        'requested_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => MaintenancePriority::class,
            'status' => MaintenanceRequestStatus::class,
            'requested_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [MaintenanceRequestStatus::Completed, MaintenanceRequestStatus::Cancelled], true);
    }

    public function canCreateWorkOrder(): bool
    {
        if ($this->isTerminal() || $this->relationLoaded('workOrder') ? $this->workOrder !== null : $this->workOrder()->exists()) {
            return false;
        }

        return true;
    }

    public function canBeCancelled(): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        $workOrder = $this->relationLoaded('workOrder') ? $this->workOrder : $this->workOrder()->first();

        if ($workOrder && $workOrder->status !== WorkOrderStatus::Cancelled) {
            return false;
        }

        return true;
    }
}
