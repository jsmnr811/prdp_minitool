<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory;

    // Table associated with the model
    protected $table = 'products';
protected $casts = [
    'start_bid_date' => 'datetime',
    'end_bid_date' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
];


    protected $fillable = [
        'lot_number',
        'rank',
        'variety',
        'green_grade',
        'origin',
        'process',
        'elevation',
        'cup_score',
        'cup_profile',
        'status',
    ];


    public function auction()
    {
        return $this->hasOne(Auction::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('lot_number', 'like', '%' . $search . '%')
                ->orWhere('variety', 'like', '%' . $search . '%')
                ->orWhere('green_grade', 'like', '%' . $search . '%')
                ->orWhere('origin', 'like', '%' . $search . '%')
                ->orWhere('process', 'like', '%' . $search . '%');
        });
    }

    public function getHighestBidPriceAttribute()
    {
        // Avoid extra query if bids are already loaded
        if ($this->relationLoaded('bids')) {
            return $this->bids->max('amount');
        }

        // Fallback: run query
        return $this->bids()->max('amount');
    }
}
