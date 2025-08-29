<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoCommodity extends Model
{
    protected $table = 'geo_commodities';

    protected $guarded = ['id'];

    public function commodity()
    {
        return $this->belongsTo(Commodity::class);
    }

    public function geoInterventions()
    {
        return $this->hasMany(GeoIntervention::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($geoCommodity) {
            // Delete all related interventions before deleting geoCommodity
            $geoCommodity->geoInterventions()->delete();
        });
    }

    public function user()
{
    return $this->belongsTo(User::class);
}

}
