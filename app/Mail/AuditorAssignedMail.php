<?php

namespace App\Mail;

use App\Models\AuditAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuditorAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public AuditAssignment $assignment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[MFTC] Jadwal Audit Telah Ditentukan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auditor-assigned',
        );
    }
}
