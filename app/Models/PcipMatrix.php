<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcipMatrix extends Model
{
    // Specify the table if it's not the plural of the model name
    protected $table = 'pcip_matrices';

    // Fillable fields for mass assignment (adjust as needed)
    protected $fillable = [
        'region_id',
        'region_name',
        'province_id',
        'province_name',
        'commodity_id',
        'commodity_name',
        'intervention_id',
        'intervention_name',
        'funding_requirement',
        'funded',
        'unfunded',
    ];

   public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'code');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'code');
    }

    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'commodity_id', 'id');
    }

    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'intervention_id', 'id');
    }

}
