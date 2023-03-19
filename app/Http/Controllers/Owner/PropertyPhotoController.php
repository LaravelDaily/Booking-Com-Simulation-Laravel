<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PropertyPhotoController extends Controller
{
    public function store(Property $property, Request $request)
    {
        $request->validate([
            'photo' => ['image', 'max:5000']
        ]);

        if ($property->owner_id != auth()->id()) {
            abort(403);
        }

        $photo = $property->addMediaFromRequest('photo')->toMediaCollection('photos');

        $position = Media::query()
            ->where('model_type', 'App\Models\Property')
            ->where('model_id', $property->id)
            ->max('position') + 1;
        $photo->position = $position;
        $photo->save();

        return [
            'filename' => $photo->getUrl(),
            'thumbnail' => $photo->getUrl('thumbnail'),
            'position' => $photo->position
        ];
    }

    public function reorder(Property $property, Media $photo, int $newPosition)
    {
        if ($property->owner_id != auth()->id() || $photo->model_id != $property->id) {
            abort(403);
        }

        $query = Media::query()
            ->where('model_type', 'App\Models\Property')
            ->where('model_id', $photo->model_id);
        if ($newPosition < $photo->position) {
            $query
                ->whereBetween('position', [$newPosition, $photo->position-1])
                ->increment('position');
        } else {
            $query
                ->whereBetween('position', [$photo->position+1, $newPosition])
                ->decrement('position');
        }
        $photo->position = $newPosition;
        $photo->save();

        return [
            'newPosition' => $photo->position
        ];
    }
}
