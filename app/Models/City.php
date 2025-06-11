<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Map;
use App\Models\ShopRequest;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the maps for the city.
     */
    public function maps()
    {
        return $this->hasMany(Map::class);
    }

    /**
     * Get the shop requests for the city.
     */
    public function shopRequests()
    {
        return $this->hasMany(ShopRequest::class);
    }
}
