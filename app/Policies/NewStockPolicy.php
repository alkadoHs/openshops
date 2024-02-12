<?php

namespace App\Policies;

use App\Models\NewStock;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NewStockPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role == 'admin' || $user->role == 'manager' || $user->role == 'superuser';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NewStock $newStock): bool
    {
        return $user->role == 'admin' || $user->role == 'manager' || $user->role == 'superuser';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role == 'admin' || $user->role == 'manager' || $user->role == 'superuser';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, NewStock $newStock): bool
    {
        return $user->role == 'admin' || $user->role == 'manager' || $user->role == 'superuser';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NewStock $newStock): bool
    {
        return $user->role == 'admin' || $user->role == 'manager' || $user->role == 'superuser';
    }

}
