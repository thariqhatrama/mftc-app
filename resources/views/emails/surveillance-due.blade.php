<h2>Pengingat Surveilans Sertifikasi</h2>

<p>Yth. Pelaku Usaha,</p>

<p>Sertifikat Pariwisata Ramah Muslim Anda akan memasuki periode anniversary dalam {{ $daysUntilAnniversary }} hari:</p>

<ul>
    <li><strong>No. Sertifikat:</strong> {{ $certificate->certificate_number }}</li>
    <li><strong>Level:</strong> {{ ucwords(str_replace('_', ' ', $certificate->level?->value ?? '')) }}</li>
</ul>

<p>Tim MFTC akan melakukan surveilans sesuai ketentuan. Pastikan usaha Anda tetap memenuhi standar yang telah disertifikasi.</p>

<p>Terima kasih,<br>Tim MFTC</p>
