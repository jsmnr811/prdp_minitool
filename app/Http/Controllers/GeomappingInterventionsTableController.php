<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\DataTables\GeomappingInterventionsDataTable;
use App\Models\GeomappingUser;

class GeomappingInterventionsTableController extends Controller
{
    public function index(GeomappingInterventionsDataTable $dataTable)
    {
        if (Auth::guard('geomapping')->check()) {
            if (Auth::guard('geomapping')->user()->role != '1') {
                return abort(403);
            }
        }
        return $dataTable->render('geomapping.iplan.intervention-list');
    }
}
