<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VendorProduct;
use Illuminate\Auth\Access\Response;

class VendorProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role == 'vendor' || $user->role == 'admin' || $user->role == 'superuser';
    }

    public function view(User $user, VendorProduct $vendorProduct): bool
    {
        return $user->id == $vendorProduct->user_id || $user->role == 'admin' || $user->role == 'superuser';;
    }

   }
