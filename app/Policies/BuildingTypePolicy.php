<?php

namespace App\Policies;

use App\Models\BuildingType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BuildingTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BuildingType $buildingType): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BuildingType $buildingType): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BuildingType $buildingType): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BuildingType $buildingType): bool
    {
        return false; // Not implementing restore for now
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BuildingType $buildingType): bool
    {
        return false; // Not implementing force delete for now
    }
}
