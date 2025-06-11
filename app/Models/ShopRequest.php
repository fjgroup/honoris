<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\City;
use App\Models\BuildingType;
use App\Models\ShopContract;

class ShopRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'city_id',
        'building_type_id',
        'notes',
        'status',
        'assigned_contract_id',
    ];

    /**
     * Get the user who made the request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the city for the shop request.
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the building type for the shop request.
     */
    public function buildingType()
    {
        return $this->belongsTo(BuildingType::class);
    }

    /**
     * Get the contract assigned to this shop request.
     */
    public function assignedContract()
    {
        return $this->belongsTo(ShopContract::class, 'assigned_contract_id');
    }
}
