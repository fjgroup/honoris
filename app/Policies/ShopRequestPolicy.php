<?php

namespace App\Policies;

use App\Models\ShopRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShopRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'captain';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ShopRequest $shopRequest): bool
    {
        return $user->role === 'admin' || $user->role === 'captain';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'captain';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ShopRequest $shopRequest): bool
    {
        return $user->role === 'admin' || $user->role === 'captain';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ShopRequest $shopRequest): bool
    {
        return $user->role === 'admin' || $user->role === 'captain';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ShopRequest $shopRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ShopRequest $shopRequest): bool
    {
        return false;
    }
}
