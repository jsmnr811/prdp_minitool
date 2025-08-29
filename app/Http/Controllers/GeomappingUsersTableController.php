<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DataTables\GeomappingUsersDataTable;

class GeomappingUsersTableController extends Controller
{
    public function index(GeomappingUsersDataTable $dataTable)
    {
        if (Auth::guard('geomapping')->check()) {
            if (Auth::guard('geomapping')->user()->role != '1') {
                return abort(403);
            }
        }
        return $dataTable->render('geomapping.iplan.user-list');
    }

    public function idCard($id)
    {
        $user = \App\Models\GeomappingUser::findOrFail($id);

        return view('geomapping.iplan.user-id-card', compact('user'));
    }

    public function verifyUser($id)
    {
        $user = \App\Models\GeomappingUser::findOrFail($id);
        return view('geomapping.iplan.user-verification', compact('user'));
    }
}
