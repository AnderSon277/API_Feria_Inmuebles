<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    public function index()
    {
        return Property::all();
    }

    public function show(Property $property)
    {
        return response()->json($property, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title' => ['required', 'string', 'max:255'],
                'area' => ['required', 'integer'],
                'bedrooms' => ['required', 'integer'],
                'bathrooms' => ['required', 'integer'],
                'livingrooms' => ['required', 'integer'],
                'kitchens' => ['required', 'integer'],
                'parkings' => ['required', 'integer'],
                'photos' => ['required', 'string'],
                'description' => ['required', 'string'],
                'address' => ['required', 'string'],
                'price' => ['required', 'integer'],
                'type' => ['required', 'string']
            ]
        );
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $property = new Property($request->all());
        $property->user_id = Auth::id();
        $property->save();

        return response()->json($property, 201);
    }

    public function update(Request $request, Property $property)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title' => ['nullable', 'string', 'max:255'],
                'area' => ['nullable', 'integer'],
                'bedrooms' => ['nullable', 'integer'],
                'bathrooms' => ['nullable', 'integer'],
                'livingrooms' => ['nullable', 'integer'],
                'kitchens' => ['nullable', 'integer'],
                'parkings' => ['nullable', 'integer'],
                'photos' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
                'address' => ['nullable', 'string'],
                'price' => ['nullable', 'integer'],
                'type' => ['nullable', 'string']
            ]
        );
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        };
        $property->update($request->all());
        return response()->json($property, 200);
    }

    public function delete(Property $property)
    {
        $property->delete();
        return response()->json(null, 204);
    }
}
