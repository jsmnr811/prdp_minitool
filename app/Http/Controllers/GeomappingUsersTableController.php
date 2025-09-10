<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\DataTables\GeomappingUsersDataTable;
use App\Models\GeomappingUser;
use Spatie\Browsershot\Browsershot;

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
         $bgPath = public_path('icons/NAFIF-ID-Template.png');
        $bgData = base64_encode(file_get_contents($bgPath));
        $bgSrc = 'data:image/png;base64,' . $bgData;
        $user = GeomappingUser::findOrFail($id);
        $logoPath = public_path('media/Scale-Up.png');
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;

        $userImagePath = $user->image && Storage::disk('public')->exists(str_replace('storage/', '', $user->image)) && file_exists(public_path($user->image))
            ? public_path($user->image)
            : storage_path('app/public/investmentforum2025/default.png');

        $userImageData = base64_encode(file_get_contents($userImagePath));
        $userImageSrc = 'data:image/png;base64,' . $userImageData;
        return view('geomapping.iplan.user-verification', compact('user', 'logoSrc', 'userImageSrc','bgSrc'));
    }

public function generateAllIds()
{
    $users = GeomappingUser::where('is_verified', 1)->get();

    foreach ($users as $user) {
        // Prepare background image
        $bgPath = public_path('icons/NAFIF-ID-Template.png');
        $bgData = base64_encode(file_get_contents($bgPath));
        $bgSrc = 'data:image/png;base64,' . $bgData;

        // Prepare logo image
        $logoPath = public_path('media/Scale-Up.png');
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;

        // Prepare user image (fallback to default)
        $userImagePath = $user->image 
            && Storage::disk('public')->exists(str_replace('storage/', '', $user->image)) 
            && file_exists(public_path($user->image))
                ? public_path($user->image)
                : storage_path('app/public/investmentforum2025/default.png');

        $userImageData = base64_encode(file_get_contents($userImagePath));
        $userImageSrc = 'data:image/png;base64,' . $userImageData;

        // Generate filename and storage path
        $fileName = 'user-id-' . $user->id . '.png';
        $storagePath = storage_path('app/public/' . $fileName);

        // Render Blade template to HTML
        $html = view('components.user-id', [
            'user' => $user,
            'logoSrc' => $logoSrc,
            'userImageSrc' => $userImageSrc,
            'bgSrc' => $bgSrc,
        ])->render();

        // Delete old file if exists
        if (file_exists($storagePath)) {
            unlink($storagePath);
        }

        // Generate PNG with Browsershot
        Browsershot::html($html)
            ->windowSize(330, 515)
            ->quality(85)
            ->setScreenshotType('png')
            ->waitUntilNetworkIdle()
            ->save($storagePath);
    }

    return redirect()->back()->with('success', 'All verified user IDs generated successfully.');
}
}
