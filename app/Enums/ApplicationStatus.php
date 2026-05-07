<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case INVOICED = 'invoiced';
    case PAYMENT_UPLOADED = 'payment_uploaded';
    case PAYMENT_VERIFIED = 'payment_verified';
    case AUDIT_READY = 'audit_ready';
    case AUDITOR_ASSIGNED = 'auditor_assigned';
    case SCHEDULE_CONFIRMED = 'schedule_confirmed';
    case AUDIT_IN_PROGRESS = 'audit_in_progress';
    case REVISION = 'revision';
    case REPORT_SUBMITTED = 'report_submitted';
    case REPORT_REJECTED = 'report_rejected';
    case APPROVED = 'approved';
    case CERTIFIED = 'certified';
    case SURVEILLANCE_FAILED = 'surveillance_failed';
    case AUTO_CANCELLED = 'auto_cancelled';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    /**
     * Display label untuk PU (bukan status DB).
     * "paid" dan "ready_for_review" TIDAK ADA sebagai enum case.
     */
    public function displayLabel(): string
    {
        return match ($this) {
            self::PAYMENT_VERIFIED => 'PAID',
            self::AUDIT_READY => 'READY FOR REVIEW',
            default => strtoupper(str_replace('_', ' ', $this->value)),
        };
    }
}
