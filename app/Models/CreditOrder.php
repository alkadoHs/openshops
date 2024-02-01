<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CreditOrder extends Model
{
    use HasFactory;

    protected $fillable = ['order_id'];


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creditOrderPayments(): HasMany
    {
        return $this->hasMany(CreditOrderPayment::class);
    }

    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Order::class);
    }
}
