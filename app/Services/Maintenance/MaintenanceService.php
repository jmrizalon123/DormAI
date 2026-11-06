<?php

namespace App\Services\Maintenance;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\WorkOrderStatus;
use App\Models\MaintenanceLog;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\MaintenanceRequestSubmittedNotification;
use App\Notifications\MaintenanceStatusUpdatedNotification;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MaintenanceService
{
    public function __construct(
        private readonly NotificationDispatcher $notificationDispatcher,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRequestForTenant(Tenant $tenant, User $reporter, array $data): MaintenanceRequest
    {
        $tenant->loadMissing('activeAssignment');

        $assignment = $tenant->activeAssignment;

        if ($assignment === null) {
            throw new InvalidArgumentException('You must have an active room assignment to submit maintenance requests.');
        }

        return $this->createRequest($tenant->organization, $reporter, [
            'tenant_id' => $tenant->id,
            'property_id' => $assignment->property_id,
            'room_id' => $assignment->room_id,
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $data['priority'] ?? MaintenancePriority::Normal->value,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRequest(Organization $organization, User $reporter, array $data): MaintenanceRequest
    {
        return DB::transaction(function () use ($organization, $reporter, $data) {
            $request = MaintenanceRequest::query()->create([
                'organization_id' => $organization->id,
                'reference_code' => $this->nextRequestCode($organization->id),
                'tenant_id' => $data['tenant_id'] ?? null,
                'property_id' => $data['property_id'],
                'room_id' => $data['room_id'] ?? null,
                'reported_by' => $reporter->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => MaintenancePriority::from($data['priority'] ?? MaintenancePriority::Normal->value),
                'status' => MaintenanceRequestStatus::Submitted,
                'requested_at' => now(),
            ]);

            $this->log(null, $request, $reporter, 'request.created', 'Maintenance request submitted.');

            $request = $request->fresh(['organization', 'tenant.user']);
            $this->notificationDispatcher->notifyOrganizationStaff(
                $organization,
                new MaintenanceRequestSubmittedNotification($request),
                ['view maintenance', 'manage maintenance'],
                'maintenance_submitted:'.$request->id,
                $request,
            );

            return $request;
        });
    }

    public function createWorkOrderFromRequest(MaintenanceRequest $request, User $creator): WorkOrder
    {
        if ($request->workOrder) {
            throw new InvalidArgumentException('A work order already exists for this request.');
        }

        return DB::transaction(function () use ($request, $creator) {
            $workOrder = WorkOrder::query()->create([
                'organization_id' => $request->organization_id,
                'maintenance_request_id' => $request->id,
                'work_order_number' => $this->nextWorkOrderNumber($request->organization_id),
                'tenant_id' => $request->tenant_id,
                'property_id' => $request->property_id,
                'room_id' => $request->room_id,
                'created_by' => $creator->id,
                'title' => $request->title,
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => WorkOrderStatus::Open,
            ]);

            $request->update([
                'status' => MaintenanceRequestStatus::WorkOrderCreated,
            ]);

            $this->log($workOrder, $request, $creator, 'work_order.created', 'Work order created from maintenance request.');

            return $workOrder;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createStandaloneWorkOrder(Organization $organization, User $creator, array $data): WorkOrder
    {
        return DB::transaction(function () use ($organization, $creator, $data) {
            $workOrder = WorkOrder::query()->create([
                'organization_id' => $organization->id,
                'work_order_number' => $this->nextWorkOrderNumber($organization->id),
                'tenant_id' => $data['tenant_id'] ?? null,
                'property_id' => $data['property_id'],
                'room_id' => $data['room_id'] ?? null,
                'created_by' => $creator->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'priority' => MaintenancePriority::from($data['priority'] ?? MaintenancePriority::Normal->value),
                'status' => WorkOrderStatus::Open,
                'scheduled_for' => $data['scheduled_for'] ?? null,
            ]);

            $this->log($workOrder, null, $creator, 'work_order.created', 'Work order opened.');

            return $workOrder;
        });
    }

    public function assign(WorkOrder $workOrder, User $actor, User $assignee): WorkOrder
    {
        if (! $workOrder->isOpen()) {
            throw new InvalidArgumentException('Closed work orders cannot be reassigned.');
        }

        if ((int) $assignee->organization_id !== (int) $workOrder->organization_id) {
            throw new InvalidArgumentException('Assignee must belong to this organization.');
        }

        $workOrder->update([
            'assigned_to' => $assignee->id,
            'status' => WorkOrderStatus::Assigned,
        ]);

        $this->log($workOrder, $workOrder->maintenanceRequest, $actor, 'work_order.assigned', "Assigned to {$assignee->name}.");

        return $workOrder->fresh(['assignee']);
    }

    public function updateStatus(WorkOrder $workOrder, User $actor, WorkOrderStatus $status, ?string $note = null): WorkOrder
    {
        if ($workOrder->status === WorkOrderStatus::Completed) {
            throw new InvalidArgumentException('Completed work orders cannot change status.');
        }

        $updates = ['status' => $status];

        if ($status === WorkOrderStatus::InProgress && $workOrder->started_at === null) {
            $updates['started_at'] = now();
        }

        $workOrder->update($updates);

        $this->log(
            $workOrder,
            $workOrder->maintenanceRequest,
            $actor,
            'work_order.status',
            $note ?? "Status changed to {$status->label()}.",
            ['status' => $status->value],
        );

        $workOrder = $workOrder->fresh(['maintenanceRequest.tenant.user']);
        $maintenanceRequest = $workOrder->maintenanceRequest;
        $tenantUser = $maintenanceRequest?->tenant?->user;
        if ($maintenanceRequest && $tenantUser) {
            $this->notificationDispatcher->notifyUser(
                $tenantUser,
                new MaintenanceStatusUpdatedNotification($maintenanceRequest, $status->label()),
                null,
                'maintenance_status',
                $maintenanceRequest,
            );
        }

        return $workOrder;
    }

    public function complete(
        WorkOrder $workOrder,
        User $actor,
        float $laborCost,
        float $partsCost,
        ?string $completionNotes = null,
    ): WorkOrder {
        if (! $workOrder->isOpen()) {
            throw new InvalidArgumentException('Work order is already closed.');
        }

        $total = round($laborCost + $partsCost, 2);

        $workOrder->update([
            'status' => WorkOrderStatus::Completed,
            'completed_at' => now(),
            'labor_cost' => $laborCost,
            'parts_cost' => $partsCost,
            'total_cost' => $total,
            'completion_notes' => $completionNotes,
        ]);

        if ($workOrder->maintenanceRequest) {
            $workOrder->maintenanceRequest->update([
                'status' => MaintenanceRequestStatus::Completed,
                'closed_at' => now(),
            ]);
        }

        $this->log(
            $workOrder,
            $workOrder->maintenanceRequest,
            $actor,
            'work_order.completed',
            'Work order completed.',
            ['total_cost' => $total],
        );

        return $workOrder->fresh();
    }

    public function cancelRequest(MaintenanceRequest $request, User $actor, string $reason): MaintenanceRequest
    {
        if ($request->workOrder && $request->workOrder->status !== WorkOrderStatus::Cancelled) {
            throw new InvalidArgumentException('Cancel the linked work order first.');
        }

        $request->update([
            'status' => MaintenanceRequestStatus::Cancelled,
            'closed_at' => now(),
        ]);

        $this->log(null, $request, $actor, 'request.cancelled', $reason);

        return $request->fresh();
    }

    private function log(
        ?WorkOrder $workOrder,
        ?MaintenanceRequest $request,
        User $user,
        string $event,
        string $message,
        ?array $meta = null,
    ): void {
        MaintenanceLog::query()->create([
            'organization_id' => $workOrder?->organization_id ?? $request?->organization_id ?? $user->organization_id,
            'work_order_id' => $workOrder?->id,
            'maintenance_request_id' => $request?->id,
            'user_id' => $user->id,
            'event' => $event,
            'message' => $message,
            'meta' => $meta,
        ]);
    }

    private function nextRequestCode(int $organizationId): string
    {
        $count = MaintenanceRequest::query()->where('organization_id', $organizationId)->count() + 1;

        return 'MR-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    private function nextWorkOrderNumber(int $organizationId): string
    {
        $count = WorkOrder::query()->where('organization_id', $organizationId)->count() + 1;

        return 'WO-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}
