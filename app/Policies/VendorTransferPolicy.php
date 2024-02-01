<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VendorTransfer;
use Illuminate\Auth\Access\Response;

class VendorTransferPolicy
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
    public function view(User $user, VendorTransfer $vendorTransfer): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role != 'vendor';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VendorTransfer $vendorTransfer): bool
    {
        return ($user->branch_id == $vendorTransfer->branch_id || $user->role == 'admin' && $user->role != 'vendor') && $vendorTransfer->status == 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VendorTransfer $vendorTransfer): bool
    {
        return ($user->branch_id == $vendorTransfer->branch_id || $user->role == 'admin') && ($vendorTransfer->status == 'pending' || $vendorTransfer->status == 'rejected');
    }
}
