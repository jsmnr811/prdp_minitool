<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidlanResponse extends Model
{
    protected $table = 'sidlan_responses';

    protected $fillable = [
        'dataset_id',
        'cluster',
        'region',
        'province',
        'group_status',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
