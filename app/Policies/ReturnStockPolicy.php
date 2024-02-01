<?php

namespace App\Policies;

use App\Models\ReturnStock;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReturnStockPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ReturnStock $returnStock): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role == 'vendor' || $user->role == 'admin' || $user->role == 'superuser';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ReturnStock $returnStock): bool
    {
        return ($user->role == 'admin' || $user->role == 'superuser' || $user->role == 'vendor') && ($returnStock->status == 'pending' || $returnStock->status == 'rejected');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ReturnStock $returnStock): bool
    {
        return ($user->role == 'admin'|| $user->role == 'superuser' || $user->role == 'vendor') && ($returnStock->status == 'pending' || $returnStock->status == 'rejected');
    }

}
