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
        return view('geomapping.iplan.user-verification', compact('user', 'logoSrc', 'userImageSrc', 'bgSrc'));
    }

    public function generateAllIds()
    {
        // Temporarily increase memory limit for 4K generation
        ini_set('memory_limit', '4096M');

        // $users = GeomappingUser::where('is_verified', 1)->get();
        $users = GeomappingUser::whereIn('id', [682,684,688,689,690,691,692,693,
        694,695,696,697,698,699,700,701,702,703,652,627])->get();


        foreach ($users as $user) {
            $fileName = 'user-id-' . $user->id . '.png';
            $directory = storage_path('app/public/nafif/by-office/');

            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $storagePath = $directory . $fileName;

            // Skip if file already exists
            if (file_exists($storagePath)) {
                continue;
            }

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

            // Render Blade template to HTML
            $html = view('components.user-id-mail', [
                'user' => $user,
                'logoSrc' => $logoSrc,
                'userImageSrc' => $userImageSrc,
                'bgSrc' => $bgSrc,
            ])->render();

            // Generate PNG with Browsershot in 4K resolution
            Browsershot::html($html)
                ->windowSize(3300, 5150)
                ->deviceScaleFactor(2)
                ->quality(100)
                ->setScreenshotType('png')
                ->waitUntilNetworkIdle()
                ->save($storagePath);

            // Free memory for next iteration
            unset($html, $bgData, $logoData, $userImageData);
            gc_collect_cycles();
        }

        return redirect()->back()->with('success', 'All verified user IDs generated successfully in 4K.');
    }
}
