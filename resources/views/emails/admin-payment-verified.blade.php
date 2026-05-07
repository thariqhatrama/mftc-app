<h2>Pembayaran Terverifikasi</h2>

<p>Yth. Admin,</p>

<p>Pembayaran telah diverifikasi untuk pengajuan berikut dan siap untuk assign auditor:</p>

<ul>
    <li><strong>PU:</strong> {{ $application->puUser?->full_name ?? 'N/A' }}</li>
    <li><strong>Scope:</strong> {{ ucwords(str_replace('_', ' ', $application->scope?->value ?? '')) }}</li>
    <li><strong>Level:</strong> {{ ucwords(str_replace('_', ' ', $application->level?->value ?? '')) }}</li>
</ul>

<p>Silakan segera assign auditor melalui panel admin.</p>

<p>Terima kasih,<br>Sistem MFTC</p>
