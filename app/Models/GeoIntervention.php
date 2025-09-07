<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoIntervention extends Model
{
    protected $table = 'geo_interventions';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = ['geo_commodity_id', 'intervention_id'];

    public function geoCommodity()
    {
        return $this->belongsTo(GeoCommodity::class);
    }
    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }

}
