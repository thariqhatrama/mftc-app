<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SlaBreachedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, array{application_id: string, status: string, overdue_days: int}>  $overdueItems
     */
    public function __construct(public Collection $overdueItems) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[MFTC] Peringatan: SLA Terlampaui',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sla-breached',
        );
    }
}
