<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Owner;
use App\Models\ShopContract;
use App\Models\ShopRequest;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'language_preference',
        'rank', // Added rank
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the owner associated with the user.
     */
    public function owner()
    {
        return $this->hasOne(Owner::class);
    }

    /**
     * Get the shop contracts updated by the user.
     */
    public function shopContractsUpdated()
    {
        return $this->hasMany(ShopContract::class, 'last_updated_by');
    }

    /**
     * Get the shop requests for the user.
     */
    public function shopRequests()
    {
        return $this->hasMany(ShopRequest::class);
    }
}
