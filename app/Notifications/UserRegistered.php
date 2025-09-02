<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\GeomappingUser;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserRegistered extends Notification implements ShouldQueue
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
        $fileName = 'user-image-' . $this->user->id . '.png';
        $storagePath = storage_path('app/public/' . $fileName);

        // Load logo image and convert to base64
        $logoPath = public_path('media/Scale-Up.png');
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;

        // Load user image and convert to base64 (check if exists, otherwise use default)
        $userImagePath = $this->user->image && Storage::disk('public')->exists(str_replace('storage/', '', $this->user->image)) && file_exists(public_path($this->user->image))
            ? public_path($this->user->image)
            : storage_path('app/public/investmentforum2025/default.png');

        $userImageData = base64_encode(file_get_contents($userImagePath));
        $userImageSrc = 'data:image/png;base64,' . $userImageData;

        // Render the Blade view into HTML with image data
        $html = view('components.user-id', [
            'user' => $this->user,
            'logoSrc' => $logoSrc,
            'userImageSrc' => $userImageSrc,
        ])->render();

        // Generate screenshot with Puppeteer via Browsershot
        Browsershot::html($html)
            ->noSandbox()
            ->windowSize(350, 566)
            ->waitUntilNetworkIdle()
            ->timeout(60)
            ->save($storagePath);

        // Send the email with image attached
        return (new MailMessage)
            ->subject('Welcome to National Agri-Fishery Investment Forum')
            ->view('emails.investment-forum-registration', [
                'user' => $this->user,
                'logoSrc' => $logoSrc
            ])
            ->attach($storagePath, [
                'as' => 'welcome-image.png',
                'mime' => 'image/png',
            ]);
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
