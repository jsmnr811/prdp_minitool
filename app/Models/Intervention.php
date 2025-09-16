<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache; 

class Intervention extends Model
{

    protected $table = 'interventions';
    protected $guarded = ['id'];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('interventions_all');
        });
    }
}
