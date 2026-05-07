<?php

namespace Database\Seeders;

use App\Models\SystemConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            // SLA (working days)
            ['sla.invoice_after_submit_days', '3', 'SLA Sales buat invoice setelah submitted'],
            ['sla.assign_auditor_after_audit_ready_days', '2', 'SLA Super Admin assign auditor'],
            ['sla.audit_start_after_assigned_days', '7', 'SLA Auditor mulai audit'],
            ['sla.revision_max_months', '3', 'Batas waktu PU memperbaiki revisi (bulan)'],
            ['sla.report_review_days', '5', 'SLA Super Admin approve laporan'],
            ['sla.certificate_issue_days', '14', 'SLA penerbitan sertifikat setelah approved'],

            // Self-assessment & invoice timers
            ['self_assessment.reminder_days', '30', 'Hari kirim reminder self-assessment belum submit'],
            ['self_assessment.auto_cancel_days', '60', 'Hari auto-cancel self-assessment belum submit'],
            ['invoice.expire_hours', '72', 'Jam invoice expired bila belum dibayar'],

            // Override approval threshold
            ['invoice.override_threshold_percent', '20', 'Threshold (%) override harga butuh approval'],

            // Biaya default per scope+level (IDR)
            ['fee.hotel.one_star', '5000000', 'Biaya dasar Hotel One Star'],
            ['fee.hotel.two_star', '8000000', 'Biaya dasar Hotel Two Star'],
            ['fee.hotel.three_star', '12000000', 'Biaya dasar Hotel Three Star'],
            ['fee.restaurant.one_star', '3000000', 'Biaya dasar Restaurant One Star'],
            ['fee.restaurant.two_star', '5000000', 'Biaya dasar Restaurant Two Star'],
            ['fee.restaurant.three_star', '8000000', 'Biaya dasar Restaurant Three Star'],
            ['fee.per_additional_site', '1500000', 'Biaya tambahan per site/cabang'],

            // Surveilans & resertifikasi
            ['surveillance.fee_percent', '50', 'Persentase biaya surveilans (% dari fee awal)'],
            ['surveillance.trigger_days_before_anniversary', '30', 'Hari sebelum anniversary trigger surveilans'],
            ['surveillance.failure_grace_days', '90', 'Hari batas surveilans tidak dilakukan -> failed'],
            ['recertification.fee_percent', '80', 'Persentase biaya resertifikasi (% dari fee awal)'],

            // Sampling multisite
            ['multisite.sampling_threshold', '10', 'Jumlah site di mana mulai berlaku sampling'],
            ['multisite.sampling_percent', '50', 'Persentase site yang diaudit (sampling)'],

            // Audit log retention
            ['audit_log.retention_years', '7', 'Retensi audit_logs dalam tahun (UU PDP)'],
        ];

        foreach ($configs as [$key, $value, $description]) {
            SystemConfig::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'description' => $description]
            );
        }
    }
}
