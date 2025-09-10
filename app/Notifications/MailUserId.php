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

       $bgPath = public_path('icons/NAFIF-ID-Template.png');
        $bgData = base64_encode(file_get_contents($bgPath));
        $bgSrc = 'data:image/png;base64,' . $bgData;
        $fileName = 'user-id-' . $this->user->id . '.png';
        $storagePath = storage_path('app/public/' . $fileName);

        // Load logo image and convert to base64
        $logoPath = public_path('media/Scale-Up.png');
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;

        // Load user image and convert to base64 (default if not exists)
        $userImagePath = $this->user->image && Storage::disk('public')->exists(str_replace('storage/', '', $this->user->image)) && file_exists(public_path($this->user->image))
            ? public_path($this->user->image)
            : storage_path('app/public/investmentforum2025/default.png');

        $userImageData = base64_encode(file_get_contents($userImagePath));
        $userImageSrc = 'data:image/png;base64,' . $userImageData;

        $html = view('components.user-id-mail', [
            'user' => $this->user,
            'logoSrc' => $logoSrc,
            'userImageSrc' => $userImageSrc,
            'bgSrc' => $bgSrc
        ])->render();

        // Delete old image if exists
        if (file_exists($storagePath)) {
            unlink($storagePath);
        }

        // Generate image with Browsershot
        Browsershot::html($html)
            ->setChromePath('/usr/bin/chromium')
            ->env([
                'HOME'            => '/tmp/apache-home',
                'XDG_CONFIG_HOME' => '/tmp/apache-home/.config',
                'XDG_CACHE_HOME'  => '/tmp/apache-home/.cache',
            ])
            ->noSandbox()
            ->addChromiumArguments([
                '--disable-dev-shm-usage',
                '--disable-setuid-sandbox',
                '--disable-gpu',
                '--disable-crash-reporter',
                '--disable-features=Crashpad',
                '--disable-breakpad', // 👈 extra to stop crashpad DB errors
                '--no-first-run',
                '--no-default-browser-check',
                '--user-data-dir=/tmp/apache-home/chrome-profile',
            ])
            ->windowSize(660, 1040)
            ->deviceScaleFactor(2)
            ->waitUntilNetworkIdle()
            ->save($storagePath);
        if ($this->user->is_blocked) {
            $salutation = ($this->user->sex === 'Male') ? 'Mr.' : 'Ms.';

            return (new MailMessage)
                ->subject('National Agri-Fishery Investment Forum')
                ->greeting('Dear ' . $salutation . ' ' . ucwords($this->user->firstname) . ' ' . ucwords($this->user->lastname) . ',')
                ->line('Thank you for your interest in the National Agri-Fishery Investment Forum to be held on September 16–18, 2025 at Palacio de Maynila, Malate, Manila.')
                ->line('We highly appreciate your willingness to participate and contribute to this important event.')
                ->line('')
                ->line('However, we regret to inform you that your registration cannot be confirmed at this time. The Forum has limited slots, and as part of the event guidelines:')
                ->line('')
                ->line('Only one official participant per office is allowed to ensure equitable representation across all provinces, or')
                ->line('')
                ->line('Priority is given to the following key officials as invited participants of the activity:')
                ->line('1. Office of the Governor – Governor')
                ->line('2. Sangguniang Panlalawigan Committee on Agriculture – Chairperson')
                ->line('3. Provincial Planning and Development Office – Provincial Planning and Development Coordinator (Head)')
                ->line('4. Provincial Agriculture Office – Provincial Agriculturist (Head)')
                ->line('5. Provincial Veterinary Office – Provincial Veterinarian (Head)')
                ->line('')
                ->line('We sincerely apologize for any inconvenience this may cause and kindly request your understanding.')
                ->line('Should there be changes in the allocation of slots or if additional participation becomes possible, we will immediately reach out to you.')
                ->line('')
                ->line('Thank you once again for your interest and support. We look forward to future opportunities where we can work together in advancing agri-fishery investments.')
                ->line('')
                ->line('')
                ->line('')
                ->line('Respectfully,')
                ->salutation('National Agri-Fishery Investment Forum Secretariat');
        } else {
            return (new MailMessage)
                ->subject('Welcome to National Agri-Fishery Investment Forum')
                ->view('emails.investment-forum-registration', [
                    'user' => $this->user,
                    'logoSrc' => $logoSrc
                ]);

                // ->attach($storagePath, [
                //     'as' => 'NAFIF-ID.png',
                //     'mime' => 'image/png',
                // ]);
        }
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
