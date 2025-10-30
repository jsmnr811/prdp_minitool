<?php

namespace App\Models\HRMS;

class Component extends BaseHRMSModel
{
    protected $table = 'components';

    protected $fillable = [
        'code',
        'name',
    ];

    public function units()
    {
        return $this->hasMany(Unit::class, 'component_id');
    }
}
