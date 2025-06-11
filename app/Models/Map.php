<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\City;
use App\Models\MapPlot;

class Map extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'image_path',
        'description',
    ];

    /**
     * Get the city that owns the map.
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the map plots for the map.
     */
    public function mapPlots()
    {
        return $this->hasMany(MapPlot::class);
    }
}
