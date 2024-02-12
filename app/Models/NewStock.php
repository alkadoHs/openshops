<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_product_id',
        'branch_id',
        'stock',
    ];


    public function mainProduct()
    {
        return $this->belongsTo(MainProduct::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
