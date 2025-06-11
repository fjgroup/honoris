<?php

namespace App\Policies;

use App\Models\Map;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MapPolicy
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
    public function view(User $user, Map $map): bool
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
    public function update(User $user, Map $map): bool
    {
        return $user->role === 'admin' || $user->role === 'captain';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Map $map): bool
    {
        return $user->role === 'admin' || $user->role === 'captain';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Map $map): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Map $map): bool
    {
        return false;
    }
}
