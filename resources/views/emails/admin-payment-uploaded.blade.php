<h2>Bukti Pembayaran Baru Diunggah</h2>

<p>Yth. Admin,</p>

<p>Pelaku usaha telah mengunggah bukti pembayaran untuk pengajuan berikut:</p>

<ul>
    <li><strong>PU:</strong> {{ $application->puUser?->full_name ?? 'N/A' }}</li>
    <li><strong>Scope:</strong> {{ ucwords(str_replace('_', ' ', $application->scope?->value ?? '')) }}</li>
    <li><strong>Level:</strong> {{ ucwords(str_replace('_', ' ', $application->level?->value ?? '')) }}</li>
</ul>

<p>Silakan lakukan verifikasi pembayaran melalui panel admin.</p>

<p>Terima kasih,<br>Sistem MFTC</p>
