<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name'];


    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function vendorTransfers(): HasMany
    {
        return $this->hasMany(VendorTransfer::class);
    }

    public function returnStocks(): HasMany
    {
        return $this->hasMany(ReturnStock::class);
    }

    public function branchTransfers(): HasMany
    {
        return $this->hasMany(BranchTransfer::class);
    }

    public function newStocks(): HasMany
    {
        return $this->hasMany(NewStock::class);
    }

}
