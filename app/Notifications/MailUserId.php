<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\GeomappingUser;
use Spatie\Browsershot\Browsershot;
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
        $html = view('components.user-id', ['user' => $this->user])->render();

        // Generate a PNG snapshot of the HTML
        Browsershot::html($html)
            ->windowSize(350, 566)
            ->waitUntilNetworkIdle() // ensures images/fonts are loaded
            ->save($storagePath);

        return (new MailMessage)
            ->subject('Your Official Event ID – National Agri-Fishery Investment Forum')
            ->greeting('Hello ' . $this->user->name . ',')
            ->line('We are excited to welcome you to the **National Agri-Fishery Investment Forum**.')
            ->line('Here are your event details:')
            ->line('- **Group Number:** ' . $this->user->group_number)
            ->line('- **Table Number:** ' . $this->user->table_number)
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
