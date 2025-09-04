<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use App\Observers\GeomappingUserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[ObservedBy([GeomappingUserObserver::class])]
class GeomappingUser extends Authenticatable
{

    use Notifiable;
    protected $table = 'geomapping_users';

    protected $fillable = [
        'image',
        'name',
        'firstname',
        'middlename',
        'lastname',
        'ext_name',
        'sex',

        'institution',
        'office',
        'designation',
        'region_id',
        'province_id',

        'email',
        'contact_number',

        'food_restriction',
        'attendance_days',

        'login_code',
        'group_number',
        'table_number',
        'is_iplan',
        'is_blocked',
        'role'
    ];

    protected $hidden = [];

    public $timestamps = true;

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'code');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'code');
    }

}
