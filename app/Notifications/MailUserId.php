<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\GeomappingUser;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Notifications\Messages\MailMessage;

class MailUserId extends Notification implements ShouldQueue
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
        $fileName = 'user-id-' . $this->user->id . '.png';
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


        $html = view('components.user-id', ['user' => $this->user, 'logoSrc' => $logoSrc, 'userImageSrc' => $userImageSrc])->render();

        if (file_exists($storagePath)) {
            unlink($storagePath);
        }
        // Generate a PNG snapshot of the HTML
        Browsershot::html($html)
            ->windowSize(350, 566)
            ->waitUntilNetworkIdle() // ensures images/fonts are loaded
            ->save($storagePath);

        return (new MailMessage)
            ->subject('Your Official Event ID – National Agri-Fishery Investment Forum')
            ->greeting('Hello ' . ucwords($this->user->name) . ',')
            ->line('We are excited to welcome you to the **National Agri-Fishery Investment Forum**.')
            ->line('Here are your event details:')
            // ->line('- **Group Number:** ' . $this->user->group_number)
            // ->line('- **Table Number:** ' . $this->user->table_number)
            ->line('Your official event ID is attached as an image file. Please bring a **printed** or **digital copy** with you for entry to the event.')
            ->line('Thank you for your participation — we look forward to seeing you at the forum!')
            ->salutation('Warm regards,')
            ->attach($storagePath, [
                'as' => 'Event-ID.png',
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
