<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Property extends Model
{
    //use HasFactory;
    use Searchable;

    protected $fillable = [
        'title',
        'area',
        'bedrooms',
        'bathrooms',
        'livingrooms',
        'kitchens',
        'parkings',
        'description',
        'address',
        'price',
        'photos',
        'type',
        'user_id'
    ];

    protected $casts = [
        'photos' => 'array'
    ];

    protected $touches = ['user'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function searchableAs()
    {
        return 'properties';
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        $array['title'] = $this->title;
        $array['description'] = $this->description;
        $array['area'] = $this->area;
        $array['bedrooms'] = $this->bedrooms;
        $array['bathrooms'] = $this->bathrooms;
        $array['livingrooms'] = $this->livingrooms;
        $array['kitchens'] = $this->kitchens;
        $array['parkings'] = $this->parkings;

        return $array;
    }
}
