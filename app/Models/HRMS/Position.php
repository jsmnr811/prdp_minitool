<?php

namespace App\Models\HRMS;

class Position extends BaseHRMSModel
{
    protected $table = 'positions';

    protected $fillable = [
        'title',
        'component_id',
        'unit_id',
    ];

    public function component()
    {
        return $this->belongsTo(Component::class, 'component_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }
}
