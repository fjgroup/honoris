<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ShopContract;
use App\Models\ShopRequest;

class BuildingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon_path',
    ];

    /**
     * Get the shop contracts for the building type.
     */
    public function shopContracts()
    {
        return $this->hasMany(ShopContract::class);
    }

    /**
     * Get the shop requests for the building type.
     */
    public function shopRequests()
    {
        return $this->hasMany(ShopRequest::class);
    }
}
