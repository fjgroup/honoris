<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MapPlot;
use App\Models\Owner;
use App\Models\BuildingType;
use App\Models\User;
use App\Models\ShopRequest;

class ShopContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'map_plot_id',
        'owner_id',
        'building_type_id',
        'assigned_to_user_id',
        'start_date',
        'end_date',
        'status',
        'last_updated_by',
    ];

    /**
     * Get the map plot associated with the shop contract.
     */
    public function mapPlot()
    {
        return $this->belongsTo(MapPlot::class);
    }

    /**
     * Get the owner of the shop contract.
     */
    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    /**
     * Get the building type for the shop contract.
     */
    public function buildingType()
    {
        return $this->belongsTo(BuildingType::class);
    }

    /**
     * Get the user assigned to the shop contract.
     */
    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Get the user who last updated the shop contract.
     */
    public function lastUpdatedByUser()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    /**
     * Get the shop request associated with this contract.
     */
    public function shopRequest()
    {
        return $this->hasOne(ShopRequest::class, 'assigned_contract_id');
    }
}
