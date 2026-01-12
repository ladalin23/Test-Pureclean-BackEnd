<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\LoyaltyCard;
use Illuminate\Auth\Access\Response;

class LoyaltyCardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, LoyaltyCard $loyaltyCard): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, LoyaltyCard $loyaltyCard): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, LoyaltyCard $loyaltyCard): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, LoyaltyCard $loyaltyCard): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, LoyaltyCard $loyaltyCard): bool
    {
        return false;
    }
}
