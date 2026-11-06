<?php

namespace App\Services\Property;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\UploadedFile;

class PropertyImageService
{
    /**
     * @param  list<UploadedFile|null>  $files
     */
    public function storeUploads(Property $property, int $organizationId, array $files): int
    {
        $stored = 0;
        $sortOrder = (int) $property->images()->max('sort_order');
        $setPrimary = ! $property->images()->exists();

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store(
                'organizations/'.$organizationId.'/properties/'.$property->id,
                'public',
            );

            $sortOrder++;

            PropertyImage::query()->create([
                'organization_id' => $organizationId,
                'property_id' => $property->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'sort_order' => $sortOrder,
                'is_primary' => $setPrimary,
            ]);

            $setPrimary = false;
            $stored++;
        }

        return $stored;
    }
}
