<?php

namespace App\Exports;

use App\Models\GeomappingUser;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GeomappingUsersExport implements FromQuery, WithHeadings, WithMapping, WithDrawings
{
    protected $users;

    public function query()
    {
        // Cache the users so we can also use them for drawings()
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
                'login_code',
                'image',
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
            'QR',
            'ID Number',
            'Image File Name',
            'Created At',
            'Updated At',
        ];
    }

    public function map($user): array
    {
        $role = match ($user->role) {
            1 => $user->is_iplan ? 'I-PLAN Administrator' : 'Administrator',
            2 => 'Participant',
            default => 'Unknown',
        };

        $days = $user->attendance_days ? array_map('trim', explode(',', $user->attendance_days)) : [];

        $day1 = in_array('Day 1', $days) ? 'Yes' : 'No';
        $day2 = in_array('Day 2', $days) ? 'Yes' : 'No';
        $day3 = in_array('Day 3', $days) ? 'Yes' : 'No';

        // Generate QR and save to file
        $qrContent = route('investment.user-verification', ['id' => $user->id]);
        $fileName  = "qr-{$user->id}.png";
        $filePath  = storage_path("app/public/qrs/{$fileName}");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(120)
            ->margin(2)
            ->generate($qrContent, $filePath);

        // Just return the file name in the column
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
            $fileName,
            $user->login_code,
            basename($user->image ?? ''),
            $user->created_at?->format('Y-m-d H:i'),
            $user->updated_at?->format('Y-m-d H:i'),
        ];
    }


    public function drawings()
    {
        $drawings = [];

        $users = GeomappingUser::all();

        foreach ($users as $index => $user) {
            $qrContent = route('investment.user-verification', ['id' => $user->id]);

            // Generate a temporary PNG QR file
            $filePath = storage_path("app/public/qrs/qr-{$user->id}.png");
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true);
            }

            QrCode::format('png')
                ->size(120)
                ->margin(2)
                ->generate($qrContent, $filePath);

            $drawing = new Drawing();
            $drawing->setName("QR {$user->id}");
            $drawing->setDescription("QR for user {$user->id}");
            $drawing->setPath($filePath);
            $drawing->setHeight(80);

            // Column W = 23rd column (QR column in your export)
            $drawing->setCoordinates('W' . ($index + 2)); // +2 = skip heading row

            $drawings[] = $drawing;
        }

        return $drawings;
    }
}
