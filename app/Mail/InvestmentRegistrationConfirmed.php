<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvestmentRegistrationConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $loginCode;
    public $userName;
    public $imagePath;

    /**
     * Create a new message instance.
     */

    public function __construct()
    {
        $this->loginCode = $loginCode;
        $this->userName = $userName;
        $this->imagePath = $imagePath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Investment Registration Confirmed',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.investment-forum-registration',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

     public function build()
    {
        return $this->subject('Registration Confirmed - PRDP Investment Forum')
                    ->markdown('emails.investment-forum-registration')
                    ->attach($this->imagePath, [
                        'as' => 'uploaded-image.jpg',
                        'mime' => 'image/jpeg',
                    ]);
    }
}
