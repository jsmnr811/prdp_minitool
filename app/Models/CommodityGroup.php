<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CommodityGroup extends Model
{
    use HasFactory;

    protected $fillable = ['commodity_id', 'group_number'];

    public function commodity()
    {
        return $this->belongsTo(Commodity::class);
    }
}
