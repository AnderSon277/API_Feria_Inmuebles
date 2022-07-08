<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
                'photos' => ['required', 'array', 'min:0'],
                'photos.*' => ['image', 'mimes:jpeg,png,jpg', 'max:5000'],
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

        //Subiendo imagenes al servidor y entregarndo el url 
        if ($request->hasFile('photos')) {
            $ListPhotos = array();
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('properties', 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                array_push($ListPhotos, Storage::disk('s3')->url($path));
            }
            $property->photos = $ListPhotos;
        }
        $property->save();
        return response()->json($property, 201);
    }

    public function update(Request $request)
    {
        $fields = $request->all();
        //validacion de campos
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
                'photos' => ['nullable', 'array', 'min:0'],
                'photos.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5000'],
                'description' => ['nullable', 'string'],
                'address' => ['nullable', 'string'],
                'price' => ['nullable', 'integer'],
                'type' => ['nullable', 'string']
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        };

        $property = Property::find($fields["id"]);

        //Actualizar datos
        $property->update($request->all());

        if (!is_null($request->photos)) {
            if ($request->hasFile('photos')) {
                $ListPhotos = array();
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('properties', 's3');
                    Storage::disk('s3')->setVisibility($path, 'public');
                    array_push($ListPhotos, Storage::disk('s3')->url($path));
                }
                $property->photos = $ListPhotos;
            }
        }

        $property->save();
        return response()->json($property, 200);
    }

    public function delete(Property $property)
    {
        $property->delete();
        return response()->json(null, 204);
    }

    public function search(Request $request)
    {
        $search = $request->search;

        if (!is_null($request->filters)) {
            $area = $request->filters['area'];
            $bedrooms = $request->filters['bedrooms'];
            $bathrooms = $request->filters['bathrooms'];
            $livingrooms = $request->filters['livingrooms'];
            $kitchens = $request->filters['kitchens'];
            $parkings = $request->filters['parkings'];

            return Property::search($search)
                ->where('area', $area)
                ->where('bedrooms', $bedrooms)
                ->where('bathrooms', $bathrooms)
                ->where('livingrooms', $livingrooms)
                ->where('kitchens', $kitchens)
                ->where('parkings', $parkings)
                ->paginate(5);
        }

        return Property::search($search)->paginate(5);
    }
}
