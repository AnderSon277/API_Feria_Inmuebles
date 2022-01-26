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

        return array(
            'title' => $array['title'],
            'description' => $array['description'],
            'area' => $array['area'],
            'bedrooms' => $array['bedrooms'],
            'bathroom' => $array['bathrooms'],
            'livingrooms' => $array['livingrooms'],
            'kitchens' => $array['kitchens'],
            'parkings' => $array['parkings']
        );
    }
}
