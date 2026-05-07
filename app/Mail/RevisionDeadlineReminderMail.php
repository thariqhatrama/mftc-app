<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RevisionDeadlineReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public int $daysRemaining = 0
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[MFTC] Pengingat Deadline Perbaikan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.revision-deadline-reminder',
        );
    }
}
