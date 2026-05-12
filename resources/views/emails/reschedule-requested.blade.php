<h2>Permintaan Reschedule Audit</h2>

<p>Yth. Admin,</p>

<p>Pelaku Usaha meminta penjadwalan ulang audit untuk pengajuan sertifikasi.</p>

<ul>
    <li><strong>ID Pengajuan:</strong> {{ $application->id }}</li>
    <li><strong>Perusahaan:</strong> {{ $application->puUser?->businessProfile?->company_name ?? 'N/A' }}</li>
    <li><strong>Jadwal Saat Ini:</strong> {{ $application->auditAssignment?->scheduled_date?->format('d M Y') ?? 'N/A' }} {{ $application->auditAssignment?->scheduled_time?->format('H:i') }}</li>
    <li><strong>Lokasi:</strong> {{ $application->auditAssignment?->location ?? 'Sesuai alamat usaha' }}</li>
</ul>

<p>Silakan review dan update jadwal melalui panel admin.</p>

<p>Terima kasih,<br>Sistem MFTC</p>
