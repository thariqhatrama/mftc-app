<h2>Perbaikan NC Diterima</h2>

<p>Yth. Auditor,</p>

<p>Pelaku Usaha telah mengirim perbaikan untuk temuan non-conformity.</p>

<ul>
    <li><strong>ID Pengajuan:</strong> {{ $application->id }}</li>
    <li><strong>ID NC:</strong> {{ $nonConformity->id }}</li>
    <li><strong>Perusahaan:</strong> {{ $application->puUser?->businessProfile?->company_name ?? 'N/A' }}</li>
</ul>

<p>Silakan review bukti perbaikan melalui panel admin.</p>

<p>Terima kasih,<br>Sistem MFTC</p>
