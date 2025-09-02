<?php

namespace App\Http\Controllers;

use App\Exports\GeomappingUsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class GeomappingUserExportController extends Controller
{
    public function exportCsv()
    {
        $fileName = 'geomapping_users_' . date('Ymd_His') . '.csv';
        return Excel::download(new GeomappingUsersExport, $fileName);
    }
}
