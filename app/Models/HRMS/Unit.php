<?php

namespace App\Models\HRMS;

class Unit extends BaseHRMSModel
{
    protected $table = 'units';

    protected $fillable = [
        'code',
        'name',
        'component_id',
    ];

    public function component()
    {
        return $this->belongsTo(Component::class, 'component_id');
    }
}
