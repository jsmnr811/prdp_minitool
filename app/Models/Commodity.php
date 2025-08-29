<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    protected $table = 'commodities';

    protected $guarded = ['id'];

    public function groups()
    {
        return $this->hasMany(CommodityGroup::class);
    }
}
