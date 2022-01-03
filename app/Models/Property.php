<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

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
}
