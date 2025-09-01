<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoOffice extends Model
{
    /** @use HasFactory<\Database\Factories\GeoOfficeFactory> */
    use HasFactory;

     protected $fillable = [
        'institution',
        'office',
    ];

}
