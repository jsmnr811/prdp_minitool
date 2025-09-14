<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NafifBlastEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $html;
    public $attachments = [];
    public $subject;

    public function __construct($subject, $html, $attachments = [])
    {
        $this->subject = $subject;
        $this->html = $html;
        $this->attachments = $attachments;
    }

    public function build()
    {
        $mail = $this->subject($this->subject)
                     ->view('emails.blank')
                     ->with(['html' => $this->html]);

        foreach ($this->attachments as $filePath => $name) {
            $mail->attach($filePath, ['as' => $name, 'mime' => 'image/png']);
        }

        return $mail;
    }
}
