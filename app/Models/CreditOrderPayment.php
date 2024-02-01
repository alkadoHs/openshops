<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditOrderPayment extends Model
{
    use HasFactory;

    protected $fillable = ['credit_order_id', 'amount', 'user_id'];


    public function creditOrder(): BelongsTo
    {
        return $this->belongsTo(CreditOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
