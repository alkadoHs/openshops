<?php

namespace App\Policies;

use App\Models\BranchTransfer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BranchTransferPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role == 'admin' || $user->role == 'superuser' || $user->role == 'seller';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BranchTransfer $branchTransfer): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role == 'admin' || $user->role == 'superuser' || $user->role == 'seller';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BranchTransfer $branchTransfer): bool
    {
        return ($user->role == 'admin' || $user->role == 'superuser' || $user->role == 'seller') && $branchTransfer->status == 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BranchTransfer $branchTransfer): bool
    {
        return $branchTransfer->status == 'pending' || $branchTransfer->status == 'rejected';
    }

}
