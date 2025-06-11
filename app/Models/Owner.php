<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ShopContract;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'contact_info',
        'language_preference',
        'is_for_all_users',
        'description',
    ];

    /**
     * Get the user that owns this owner profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shop contracts for the owner.
     */
    public function shopContracts()
    {
        return $this->hasMany(ShopContract::class);
    }
}
