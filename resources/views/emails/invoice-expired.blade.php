<h2>Invoice Kedaluwarsa</h2>

<p>Yth. Pelaku Usaha,</p>

<p>Invoice berikut telah kedaluwarsa karena tidak ada pembayaran dalam batas waktu yang ditentukan:</p>

<ul>
    <li><strong>No. Invoice:</strong> {{ $invoice->invoice_number }}</li>
    <li><strong>Jumlah:</strong> Rp {{ number_format($invoice->amount, 0, ',', '.') }}</li>
</ul>

<p>Silakan ajukan kembali pengajuan sertifikasi jika masih berminat.</p>

<p>Terima kasih,<br>Tim MFTC</p>
