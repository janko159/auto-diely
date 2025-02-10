<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Part;
use Illuminate\Http\Request;

class PartsController extends Controller
{
    public function store(Request $request, Car $car)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'serialnumber' => 'required|string|max:255'
        ]);

        $part = $car->parts()->create($data);

        return response()->json([
            'message' => 'Part was added to database',
            'part' => $part
        ], 201);
    }

    public function update(Request $request, Car $car, Part $part)
    {
        if ($part->car_id !== $car->id) {
            return response()->json([
                'error' => 'Part is not from this car'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'serialnumber' => 'required|string|max:255'
        ]);

        $part->update($validated);

        return response()->json([
            'message' => 'Success',
            'part' => $part
        ], 200);
    }

    public function delete(Part $part)
    {
        try {
            $part->delete();
            return response()->json([
                'message' => 'delete was succesfull'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e
            ]);
        }
    }
}
