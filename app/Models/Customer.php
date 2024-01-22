<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory;


    protected $fillable = ['branch_id','name', 'contact'];


    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Str::title($value),
            set: fn (string $value) => Str::lower($value),
        );
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
