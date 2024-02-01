<?php

namespace App\Policies;

use App\Models\CreditOrderPayment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CreditOrderPaymentPolicy
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
    public function view(User $user, CreditOrderPayment $creditOrderPayment): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CreditOrderPayment $creditOrderPayment): bool
    {
        return $user->role == 'admin' || $user->role == 'superuser';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CreditOrderPayment $creditOrderPayment): bool
    {
        return $user->role == 'admin' || $user->role == 'superuser';
    }

}
