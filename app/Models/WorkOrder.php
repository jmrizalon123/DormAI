<?php

namespace App\Models;

use App\Enums\MaintenancePriority;
use App\Enums\WorkOrderStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'maintenance_request_id',
        'work_order_number',
        'tenant_id',
        'property_id',
        'room_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'priority',
        'status',
        'scheduled_for',
        'started_at',
        'completed_at',
        'labor_cost',
        'parts_cost',
        'total_cost',
        'completion_notes',
    ];

    protected function casts(): array
    {
        return [
            'priority' => MaintenancePriority::class,
            'status' => WorkOrderStatus::class,
            'scheduled_for' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'labor_cost' => 'decimal:2',
            'parts_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, [WorkOrderStatus::Completed, WorkOrderStatus::Cancelled], true);
    }
}
