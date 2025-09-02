<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\DataTables\GeomappingUsersDataTable;
use App\Models\GeomappingUser;

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
}
