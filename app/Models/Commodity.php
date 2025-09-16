<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache; 

class Commodity extends Model
{
    protected $table = 'commodities';

    protected $guarded = ['id'];

    public function groups()
    {
        return $this->hasMany(CommodityGroup::class);
    }

    protected static function booted()
{
    static::saved(function () {
        Cache::forget('commodities_all');
    });
}

}
