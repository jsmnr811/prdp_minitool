<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\GeomappingUser;
use Spatie\Browsershot\Browsershot;
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

        // Render the blade view to HTML
        $html = view('components.user-id', ['user' => $this->user])->render();

        // Generate PNG with fixed window size
        Browsershot::html($html)
            ->windowSize(350, 566)
            ->waitUntilNetworkIdle() // ensure all images load
             ->waitFor(4000)
            ->timeout(60) // increase timeout
            ->save($storagePath);

        return (new MailMessage)
            ->subject('Welcome to Our Application')
            ->view('emails.investment-forum-registration', ['user' => $this->user])
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
