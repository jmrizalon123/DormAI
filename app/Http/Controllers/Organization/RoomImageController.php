<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomImage;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomImageController extends Controller
{
    public function store(Request $request, Room $room, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $path = $validated['image']->store(
            'organizations/'.$organizationContext->ensureId().'/rooms/'.$room->id,
            'public',
        );

        $isPrimary = ! $room->images()->exists();

        RoomImage::query()->create([
            'organization_id' => $organizationContext->ensureId(),
            'room_id' => $room->id,
            'path' => $path,
            'original_name' => $validated['image']->getClientOriginalName(),
            'sort_order' => (int) $room->images()->max('sort_order') + 1,
            'is_primary' => $isPrimary,
        ]);

        return back()->with('success', 'Room image uploaded.');
    }

    public function destroy(Room $room, RoomImage $roomImage): RedirectResponse
    {
        $this->authorize('update', $room);

        if ((int) $roomImage->room_id !== (int) $room->id) {
            abort(404);
        }

        Storage::disk('public')->delete($roomImage->path);
        $roomImage->delete();

        return back()->with('success', 'Image removed.');
    }
}
