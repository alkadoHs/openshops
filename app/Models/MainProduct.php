<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MainProduct extends Model
{
    use HasFactory;


    protected $fillable = ['name', 'buy_price', 'retail_price', 'whole_price'];


    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Str::title($value),
            set: fn (string $value) => Str::lower($value),
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function newStocks(): HasMany
    {
        return $this->hasMany(NewStock::class);
    }

}
