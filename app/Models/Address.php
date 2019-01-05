<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //
    protected $fillable = [
        'name', 'region_id', 'city_id'
    ];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function coordinates()
    {
        return $this->hasMany('App\Models\Coordinate');
    }

}
