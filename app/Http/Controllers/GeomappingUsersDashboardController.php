<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use App\Models\GeomappingUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\DataTables\PaxPerProvinceDataTable;
use App\DataTables\GeomappingUsersDataTable;

class GeomappingUsersDashboardController extends Controller
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
        $user = GeomappingUser::findOrFail($id);

        return view('geomapping.iplan.user-id-card', compact('user'));
    }

    public function verifyUser($id)
    {
        $user = GeomappingUser::findOrFail($id);
        $logoPath = public_path('media/Scale-Up.png');
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;

        $userImagePath = $user->image && Storage::disk('public')->exists(str_replace('storage/', '', $user->image)) && file_exists(public_path($user->image))
            ? public_path($user->image)
            : storage_path('app/public/investmentforum2025/default.png');

        $userImageData = base64_encode(file_get_contents($userImagePath));
        $userImageSrc = 'data:image/png;base64,' . $userImageData;
        return view('geomapping.iplan.user-verification', compact('user', 'logoSrc', 'userImageSrc'));
    }

    public function dashboard(Request $request)
    {
        $query = Region::with('provinces');

        if ($request->has('region_select')) {
            if (strtolower($request->get('region_select')) != 'all') {
                $query->where('code', $request->get('region_select'));
            }
        }
        if ($request->has('province_select')) {
            if (strtolower($request->get('province_select')) != 'all') {
                $query->whereHas('provinces', function ($q) use ($request) {
                    $q->where('code', $request->get('province_select'));
                });
            }
        }
        $regions = $query->get();

        return view('geomapping.iplan.user-list-dashboard', compact('regions'));
    }
}
