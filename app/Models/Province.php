<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Province extends Authenticatable
{
    protected $table = 'provinces';

    protected $fillable = [];

    protected $hidden = [];

    public $timestamps = true;

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_code', 'code');
    }

    public function paxCount($instituionName, $officeName,$verified = false)
    {
        $query = GeomappingUser::where('province_id', $this->code)->where('institution', $instituionName)->where('office', $officeName);
        if ($verified) {
            $query->where('is_verified', $verified);
        }
        return $query->count();
    }

        public function pcipMatrices()
    {
        return $this->hasMany(PcipMatrix::class, 'province_id', 'code');
    }

    public function geoCommodities()
{
    return $this->hasMany(GeoCommodity::class, 'province_id', 'code');
}

}
