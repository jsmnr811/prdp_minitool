<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Region extends Authenticatable
{
    protected $table = 'regions';

    protected $fillable = [];

    protected $hidden = [];

    public $timestamps = true;


    public function provinces()
    {
        return $this->hasMany(Province::class, 'region_code', 'code');
    }
    public function pcip_matrices()
    {
        return $this->hasMany(PcipMatrix::class, 'region_id', 'code');
    }
}
