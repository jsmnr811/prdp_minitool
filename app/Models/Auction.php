<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    protected $fillable = [
        'product_id',
        'starting_bid_price',
        'lot_size',
        'start_bid_date',
        'end_bid_date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
