<?php

namespace App\Models\HRMS;

class Employee extends BaseHRMSModel
{
    protected $table = 'employees';

    protected $fillable = [
        'employee_no',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'gender',
        'birth_date',
        'email',
        'contact_no',
        'address',
        'component_id',
        'unit_id',
        'position_id',
        'employment_status',
        'date_hired',
        'date_ended',
    ];

    public function component()
    {
        return $this->belongsTo(Component::class, 'component_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
}
