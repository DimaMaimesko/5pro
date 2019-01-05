<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'name'
    ];

    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'regions_addresses');
    }

}
