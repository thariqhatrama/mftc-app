<h2>Invoice Pengajuan Sertifikasi MFTC</h2>

<p>Yth. Pelaku Usaha,</p>

<p>Invoice untuk pengajuan sertifikasi Anda telah dibuat:</p>

<ul>
    <li><strong>No. Invoice:</strong> {{ $invoice->invoice_number }}</li>
    <li><strong>Jumlah:</strong> Rp {{ number_format($invoice->amount, 0, ',', '.') }}</li>
</ul>

<p>Silakan lakukan pembayaran sesuai instruksi pada halaman pengajuan Anda.</p>

<p>Terima kasih,<br>Tim MFTC</p>
