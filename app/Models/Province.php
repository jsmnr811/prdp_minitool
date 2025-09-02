<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Province extends Authenticatable
{
    protected $table = 'provinces';

    protected $fillable = [];

    protected $hidden = [];

    public $timestamps = true;

    
}
