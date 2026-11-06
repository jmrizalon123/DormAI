<?php

namespace App\Services\Property;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Property;
use App\Models\Room;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PropertyHierarchyValidator
{
    public function ensureBuildingOnProperty(Building $building, Property $property): void
    {
        if ((int) $building->property_id !== (int) $property->id) {
            throw new NotFoundHttpException;
        }
    }

    public function ensureFloorOnBuilding(Floor $floor, Building $building): void
    {
        if ((int) $floor->building_id !== (int) $building->id) {
            throw new NotFoundHttpException;
        }
    }

    public function ensureRoomOnFloor(Room $room, Floor $floor): void
    {
        if ((int) $room->floor_id !== (int) $floor->id) {
            throw new NotFoundHttpException;
        }
    }

    public function ensureRoomOnProperty(Room $room, Property $property): void
    {
        if ((int) $room->property_id !== (int) $property->id) {
            throw new NotFoundHttpException;
        }
    }
}
