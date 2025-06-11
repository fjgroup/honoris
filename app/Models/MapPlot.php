<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Map;
use App\Models\ShopContract;

class MapPlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'map_id',
        'plot_identifier',
        'coord_x',
        'coord_y',
        'width',
        'height',
        'is_active',
        'notes',
    ];

    /**
     * Get the map that owns the plot.
     */
    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    /**
     * Get the shop contract associated with the map plot.
     */
    public function shopContract()
    {
        return $this->hasOne(ShopContract::class);
    }
}
