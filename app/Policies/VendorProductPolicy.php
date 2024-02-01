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

   }
