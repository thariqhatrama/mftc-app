<h2>Jadwal Audit Telah Ditentukan</h2>

<p>Yth. Bapak/Ibu,</p>

<p>Jadwal audit sertifikasi telah ditentukan:</p>

<ul>
    <li><strong>Tanggal:</strong> {{ $assignment->scheduled_date?->format('d M Y') }}</li>
    <li><strong>Waktu:</strong> {{ $assignment->scheduled_time?->format('H:i') }} WIB</li>
    <li><strong>Lokasi:</strong> {{ $assignment->location ?? 'Sesuai alamat usaha' }}</li>
</ul>

<p>Silakan konfirmasi atau ajukan reschedule melalui halaman pengajuan Anda.</p>

<p>Terima kasih,<br>Tim MFTC</p>
