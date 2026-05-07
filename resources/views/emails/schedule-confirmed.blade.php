<h2>Jadwal Audit Dikonfirmasi</h2>

<p>Yth. Auditor,</p>

<p>Pelaku usaha telah mengkonfirmasi jadwal audit berikut:</p>

<ul>
    <li><strong>Tanggal:</strong> {{ $assignment->scheduled_date?->format('d M Y') }}</li>
    <li><strong>Waktu:</strong> {{ $assignment->scheduled_time?->format('H:i') }} WIB</li>
    <li><strong>Lokasi:</strong> {{ $assignment->location ?? 'Sesuai alamat usaha' }}</li>
</ul>

<p>Terima kasih,<br>Sistem MFTC</p>
