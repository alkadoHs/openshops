<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    
    public function viewAny(User $user): bool
    {
        return $user->role !== 'vendor';
    }

   
    public function view(User $user, Product $product): bool
    {
        return $user->branch_id == $product->branch_id;
    }


    public function create(User $user): bool
    {
        return $user->role == 'admin' || $user->role == 'superuser';
    }

   
    public function update(User $user, Product $product): bool
    {
        return $user->role == 'admin' || $user->role == 'seller';
    }

  
    public function delete(User $user, Product $product): bool
    {
        return $user->role == 'admin' || $user->role == 'superuser';
    }



   }
