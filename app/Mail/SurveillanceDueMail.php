<?php

namespace App\Mail;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SurveillanceDueMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Certificate $certificate,
        public int $daysUntilAnniversary = 0
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[MFTC] Pengingat Surveilans Sertifikasi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.surveillance-due',
        );
    }
}
