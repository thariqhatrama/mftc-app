<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\NonConformity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RevisionSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public NonConformity $nonConformity,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[MFTC] Perbaikan NC Diterima',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.revision-submitted',
        );
    }
}
