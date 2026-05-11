<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Exceptions\InvalidStatusTransitionException;
use App\Mail\CertificateIssuedMail;
use App\Models\Application;
use App\Models\Certificate;
use App\Models\SystemConfig;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CertificateService
{
    public function __construct(
        private readonly StatusTransitionService $statusTransition,
        private readonly AuditLogService $auditLog,
    ) {}

    public function generate(Application $application, User $actor): Certificate
    {
        if ($application->status !== ApplicationStatus::APPROVED) {
            throw new InvalidStatusTransitionException('Sertifikat hanya dapat dibuat untuk aplikasi berstatus approved.');
        }

        return DB::transaction(function () use ($application, $actor): Certificate {
            $application->loadMissing('puUser.businessProfile', 'sites');

            $issuedAt = now();
            $validUntil = $issuedAt->copy()->addYears(5);
            $certificateNumber = $this->generateCertificateNumber($application, (int) $issuedAt->format('Y'));

            $certificate = Certificate::query()->create([
                'application_id' => $application->id,
                'certificate_number' => $certificateNumber,
                'level' => $application->level,
                'issued_at' => $issuedAt,
                'valid_until' => $validUntil,
            ]);

            try {
                $pdf = Pdf::loadView('pdf.certificate', [
                    'certificate' => $certificate,
                    'application' => $application,
                    'site' => $application->sites->first(),
                    'levelLabel' => $this->levelLabel($application->level),
                    'levelEnglish' => $this->levelEnglish($application->level),
                    'issuedAt' => $certificate->issued_at->format('d/m/Y'),
                    'validUntil' => $certificate->valid_until->format('d/m/Y'),
                    'issuedAtEn' => $certificate->issued_at->format('Y/m/d'),
                    'validUntilEn' => $certificate->valid_until->format('Y/m/d'),
                    'directorName' => SystemConfig::get('director_name', 'Jobi Triananda'),
                    'directorTitle' => SystemConfig::get('director_title', 'Direktur Utama'),
                ]);
                $pdf->setPaper('A4', 'portrait');

                $path = sprintf('certificates/%s/%s.pdf', $issuedAt->format('Y'), $certificateNumber);
                Storage::disk('local')->put($path, $pdf->output());

                $certificate->update(['certificate_pdf_url' => $path]);
            } catch (\Throwable $e) {
                Log::error('Certificate PDF generation failed: '.$e->getMessage());
                // Tetap biarkan sertifikat ter-create di DB walau PDF gagal
            }

            $application->update([
                'certificate_number' => $certificateNumber,
                'certified_at' => $issuedAt,
                'valid_until' => $validUntil,
            ]);

            $this->statusTransition->transition($application, ApplicationStatus::CERTIFIED->value, $actor);

            try {
                Mail::queue(new CertificateIssuedMail($certificate));
            } catch (\Throwable $e) {
                Log::error('Certificate Email failed: '.$e->getMessage());
            }

            $this->auditLog->log(
                action: 'certificate_generated',
                entityType: 'certificate',
                entityId: $certificate->id,
                oldStatus: ApplicationStatus::APPROVED->value,
                newStatus: ApplicationStatus::CERTIFIED->value,
                actor: $actor,
            );

            return $certificate->refresh();
        });
    }

    private function generateCertificateNumber(Application $application, int $year): string
    {
        $sequence = Certificate::query()
            ->whereYear('issued_at', $year)
            ->whereHas('application', fn ($query) => $query->where('scope', $application->scope->value))
            ->lockForUpdate()
            ->get(['id'])
            ->count() + 1;

        return sprintf(
            'MFTC-%05d-%05d-%02d',
            $this->scopeCode($application),
            $sequence,
            $year % 100,
        );
    }

    private function scopeCode(Application $application): int
    {
        return match ($application->scope->value) {
            'hotel' => 1,
            'restaurant' => 2,
            'travel' => 3,
            'retail' => 4,
            'area' => 5,
            'terminal' => 6,
            'health_therapy' => 7,
            'mice' => 8,
            'swimming_pool' => 9,
            'hospital' => 10,
        };
    }

    private function levelLabel(CertificationLevel $level): string
    {
        return match ($level) {
            CertificationLevel::ONE_STAR => 'Bintang Satu',
            CertificationLevel::TWO_STAR => 'Bintang Dua',
            CertificationLevel::THREE_STAR => 'Bintang Tiga',
        };
    }

    private function levelEnglish(CertificationLevel $level): string
    {
        return match ($level) {
            CertificationLevel::ONE_STAR => 'One Star',
            CertificationLevel::TWO_STAR => 'Two Star',
            CertificationLevel::THREE_STAR => 'Three Star',
        };
    }
}
