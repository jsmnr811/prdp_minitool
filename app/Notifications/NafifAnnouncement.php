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


            return (new MailMessage)
                ->subject('Advisory on the National Agri-Fishery Investment Forum (NAFIF) 2025')
                ->view('emails.nafif-announcement', [
                    'user' => $this->user,
                    'bannerScr' => $bannerScr
                ]);
                // ->attach($storagePath, [
                //     'as' => 'NAFIF-ID.png',
                //     'mime' => 'image/png',
                // ]);

    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
