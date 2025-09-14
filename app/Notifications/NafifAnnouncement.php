<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\GeomappingUser;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Barryvdh\Snappy\Facades\SnappyImage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Notifications\Messages\MailMessage;

class NafifAnnouncement extends Notification implements ShouldQueue
{
    use Queueable;

    public GeomappingUser $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(GeomappingUser $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */


    public function toMail(object $notifiable): MailMessage
    {

        $bannerPath = public_path('icons/NAFIF-Email-Banner.png');
        $bannerData = base64_encode(file_get_contents($bannerPath));
        $bannerScr = 'data:image/png;base64,' . $bannerData;

        $bgPath = public_path('icons/NAFIF-ID-Template.png');
        $bgData = base64_encode(file_get_contents($bgPath));
        $bgSrc = 'data:image/png;base64,' . $bgData;

        $fileName = 'user-id-' . $this->user->id . '.png';
        $storagePath = storage_path('app/public/' . $fileName);

        // Load logo image
        $logoPath = public_path('media/Scale-Up.png');
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;

        // Load user image (fallback to default if not uploaded)
        $userImagePath = $this->user->image
            && Storage::disk('public')->exists(str_replace('storage/', '', $this->user->image))
            && file_exists(public_path($this->user->image))
            ? public_path($this->user->image)
            : storage_path('app/public/investmentforum2025/default.png');

        $userImageData = base64_encode(file_get_contents($userImagePath));
        $userImageSrc = 'data:image/png;base64,' . $userImageData;

        // Render blade to HTML
        $html = view('components.user-id', [
            'user' => $this->user,
            'logoSrc' => $logoSrc,
            'userImageSrc' => $userImageSrc,
            'bgSrc' => $bgSrc,
            'bannerScr' => $bannerScr
        ])->render();

        // Delete old file if exists
        if (file_exists($storagePath)) {
            unlink($storagePath);
        }

        // ✅ Use Browsershot to generate PNG
        Browsershot::html($html)
            ->windowSize(330, 530)
            ->quality(85) // works for jpeg/webp, ignored for png
            ->setScreenshotType('png') // or 'jpeg'
            ->waitUntilNetworkIdle()
            ->save($storagePath);

        return (new MailMessage)
            ->subject('Advisory on the National Agri-Fishery Investment Forum (NAFIF) 2025')
            ->view('emails.nafif-announcement', [
                'user' => $this->user,
                 'bannerPath' => $bannerPath,
            ])
            ->attach($storagePath, [
                'as' => 'NAFIF-ID.png',
                'mime' => 'image/png',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
