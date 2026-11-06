<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDocument extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'tenant_id',
        'category',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function diskPath(): string
    {
        return $this->path;
    }
}
