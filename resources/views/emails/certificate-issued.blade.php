<h2>Sertifikat Pariwisata Ramah Muslim Diterbitkan</h2>

<p>Yth. Pelaku Usaha,</p>

<p>Selamat! Sertifikat Pariwisata Ramah Muslim Anda telah diterbitkan:</p>

<ul>
    <li><strong>No. Sertifikat:</strong> {{ $certificate->certificate_number }}</li>
    <li><strong>Level:</strong> {{ ucwords(str_replace('_', ' ', $certificate->level?->value ?? '')) }}</li>
    <li><strong>Berlaku Hingga:</strong> {{ $certificate->valid_until?->format('d M Y') }}</li>
</ul>

<p>Anda dapat mengunduh sertifikat melalui halaman pengajuan Anda.</p>

<p>Terima kasih,<br>Tim MFTC</p>
