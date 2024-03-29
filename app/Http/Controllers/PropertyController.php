<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    public function index()
    {
        $id = Auth::user()->getAuthIdentifier();
        $properties =  Property::where("user_id", "=", $id)->get();
        return $properties;
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
                'photos' => ['required', 'array', 'min:0', 'max:10'],
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

    public function update(Request $request, Property $property)
    {
        $user_id = $property->user_id;
        //$fields = $request->all();
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

        //$property = Property::find($fields["id"]);
        if ($user_id != Auth::id()) {
            return response()->json(['Not authorized'], 401);
        }
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
        $user_id = $property->user_id;
        if ($user_id === Auth::id()) {
            $property->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['Not authorized'], 401);
        }
    }

    public function searchEngine(Request $request)
    {

        $clauses = [];

        $params = $request->all();

        foreach ($params as $key => $value)
            if ($key === 'title' || $key === 'description' || $key === 'address') {

                $clauses[] = [$key, 'LIKE', '%' . $value . '%'];
            } else {

                if ($key === 'page') continue;
                $clauses[] = [$key, '>=', $value];
            }

        return Property::where($clauses)->paginate(5);
    }
}
