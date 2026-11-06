<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToOrganization;
use Database\Factories\RoomTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    /** @use HasFactory<RoomTypeFactory> */
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
