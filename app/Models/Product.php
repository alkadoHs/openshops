<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['main_product_id', 'branch_id', 'stock', 'stock_limit', 'new_stock', 'damages'];

    protected function stock(): Attribute
    {
        return Attribute::make(
            get: fn (int $value, array $attributes) => $value - $attributes['damages'],
        );
    }

    public function mainProduct(): BelongsTo
    {
        return $this->belongsTo(MainProduct::class);
    }


    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function vendorTransfers(): HasMany
    {
        return $this->hasMany(VendorTransfer::class);
    }

    
    
    public function vendorProducts(): HasMany
    {
        return $this->hasMany(VendorProduct::class);
    }

    public function returnStocks(): HasMany
    {
        return $this->hasMany(ReturnStock::class);
    }

    public function branchTransfers(): HasMany
    {
        return $this->hasMany(BranchTransfer::class);
    }

}
