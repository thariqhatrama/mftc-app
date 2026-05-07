<h2>Peringatan: SLA Terlampaui</h2>

<p>Yth. Super Admin,</p>

<p>Beberapa pengajuan telah melewati batas SLA yang ditentukan:</p>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>Application ID</th>
            <th>Status</th>
            <th>Overdue (hari)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($overdueItems as $item)
        <tr>
            <td>{{ $item['application_id'] }}</td>
            <td>{{ strtoupper(str_replace('_', ' ', $item['status'])) }}</td>
            <td>{{ $item['overdue_days'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<p>Silakan tindak lanjuti melalui panel admin.</p>

<p>Terima kasih,<br>Sistem MFTC</p>
