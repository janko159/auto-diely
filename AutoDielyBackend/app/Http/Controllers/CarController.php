<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = $request->input('filter', '');

        $query = Car::query();

        $query = match ($filter) {
            'is_registered' => $query->isRegistered(),
            'alfabetically' => $query->alphabetically(),
            default => $query->latest(),
        };

        $cars = $query->get();

        return response()->json([
            'cars' => $cars,
            'message' => 'List of cars'
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_registered' => 'nullable|boolean',
            'registration_number' => 'nullable|required_if:is_registered,1|string|max:255',
        ]);

        Car::create($data);
        return response()->json([
            'message' => 'saved'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Car $car, Request $request)
    {
        try {
            $filter = $request->input('parts_filter', '');


            $car->load(['parts' => function ($query) use ($filter) {
                if ($filter) {
                    $query->orderBy('name', 'asc');
                } else {
                    $query->latest();
                }
            }]);
            return response()->json([
                'car' => $car,
                'message' => 'Success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Car $car)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_registered' => 'required|boolean',
            'registration_number' => 'nullable|required_if:is_registered,1|string|max:255',
        ]);

        if (!$validated['is_registered']) {
            $validated['registration_number'] = null;
        }

        $car->update($validated);

        return response()->json([
            'message' => 'car was updated'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Car $car)
    {
        try {
            $car->delete();
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
