<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [10, 30, 60];

    public function __construct(public string $name) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to DevOps Practice Hub');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.welcome');
    }
}
