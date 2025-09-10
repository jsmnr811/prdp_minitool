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
                'role',
                'is_iplan',
                'group_number',
                'table_number',
                'room_assignment',
                'is_livein',
                'is_verified', 
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
            'Attendance Day 1',
            'Attendance Day 2',
            'Attendance Day 3',
            'Role',
            'Group Number',
            'Table Number',
            'Room Assignment',
            'Is Live In?',
            'Is Verified?',
            'Created At',
            'Updated At',
        ];
    }

    public function map($user): array
    {
        if ($user->role == 1) {
            $role = $user->is_iplan ? 'I-PLAN Administrator' : 'Administrator';
        } elseif ($user->role == 2) {
            $role = 'Participant';
        } else {
            $role = 'Unknown';
        }

        // Convert attendance string ("Day 1, Day 2, Day 3") into array
        $days = $user->attendance_days
            ? array_map('trim', explode(',', $user->attendance_days))
            : [];

        // Create columns for Day 1, Day 2, Day 3
        $day1 = in_array('Day 1', $days) ? 'Yes' : 'No';
        $day2 = in_array('Day 2', $days) ? 'Yes' : 'No';
        $day3 = in_array('Day 3', $days) ? 'Yes' : 'No';

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
            $day1,
            $day2,
            $day3,
            $role,
            $user->group_number,
            $user->table_number,
            $user->room_assignment,
            $user->is_livein ? 'Yes' : 'No',
            $user->is_verified ? 'Yes' : 'No',
            $user->created_at?->format('Y-m-d H:i'),
            $user->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
