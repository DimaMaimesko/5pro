<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coordinate extends Model
{
    protected $fillable = [
        'lat', 'lng', 'address_id'
    ];

    public function address()
    {
        return $this->belongsTo('App\Models\Address');
    }


}
