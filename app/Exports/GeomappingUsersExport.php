<?php

namespace App\Exports;

use App\Models\GeomappingUser;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GeomappingUsersExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return GeomappingUser::query()
            ->select([
                'id',
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
                'group_number',
                'table_number',
                'created_at',
                'updated_at',
            ])
            ->with(['region', 'province']);
    }

    public function headings(): array
    {
        return [
            'ID',
            'First Name',
            'Middle Name',
            'Last Name',
            'Name Extension',
            'Sex',
            'Institution',
            'Office',
            'Designation',
            'Region',
            'Province',
            'Email',
            'Contact Number',
            'Food Restriction',
            'Attendance Days',
            'Group Number',
            'Table Number',
            'Created At',
            'Updated At',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->firstname,
            $user->middlename,
            $user->lastname,
            $user->name_extension,
            $user->sex,
            $user->institution,
            $user->office,
            $user->designation,
            optional($user->region)->abbr,
            optional($user->province)->name,
            $user->email,
            $user->contact_number,
            $user->food_restriction,
            $user->attendance_days,
            $user->group_number,
            $user->table_number,
            $user->created_at?->format('Y-m-d H:i'),
            $user->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
