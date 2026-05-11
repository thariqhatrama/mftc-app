<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Certificate {{ $certificate->certificate_number }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 portrait;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #1f2933;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            background: #ffffff;
        }

        .page {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            padding: 17mm 18mm 15mm;
            overflow: hidden;
            background:
                radial-gradient(circle at 18% 18%, rgba(26, 92, 58, 0.035) 0, rgba(26, 92, 58, 0.035) 1px, transparent 1px),
                radial-gradient(circle at 82% 26%, rgba(196, 154, 62, 0.035) 0, rgba(196, 154, 62, 0.035) 1px, transparent 1px),
                linear-gradient(135deg, rgba(26, 92, 58, 0.018), transparent 42%, rgba(196, 154, 62, 0.02));
            background-size: 7mm 7mm, 9mm 9mm, 100% 100%;
        }

        .watermark {
            position: absolute;
            top: 95mm;
            left: 50%;
            width: 118mm;
            height: 118mm;
            margin-left: -59mm;
            border: 5mm solid rgba(26, 92, 58, 0.08);
            border-radius: 50%;
            color: rgba(26, 92, 58, 0.08);
            font-size: 44pt;
            font-weight: 700;
            letter-spacing: 5pt;
            line-height: 108mm;
            text-align: center;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        .revision-code {
            position: absolute;
            top: 9mm;
            right: 11mm;
            width: 24mm;
            padding: 2mm 0;
            border: 0.4pt solid #243b2e;
            font-size: 8pt;
            font-weight: 700;
            text-align: center;
        }

        .logos-row {
            display: table;
            width: 100%;
            margin-bottom: 5mm;
        }

        .logo-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .logo-cell.right {
            text-align: right;
        }

        .logo-placeholder {
            display: inline-block;
            min-width: 42mm;
            height: 12mm;
            padding: 2.5mm 4mm;
            border: 0.8pt solid #d6dde2;
            color: #1a5c3a;
            font-size: 13pt;
            font-weight: 800;
            letter-spacing: 0.8pt;
            line-height: 6mm;
            text-align: center;
        }

        .sics-logo {
            width: 37mm;
            height: 37mm;
            margin: 0 auto 5mm;
            border: 2.2pt solid #1a5c3a;
            border-radius: 50%;
            color: #1a5c3a;
            text-align: center;
        }

        .sics-logo .sics-title {
            display: block;
            margin-top: 8mm;
            font-size: 17pt;
            font-weight: 800;
            letter-spacing: 1.1pt;
            line-height: 1;
        }

        .sics-logo .sics-subtitle {
            display: block;
            margin-top: 1.7mm;
            font-size: 5.9pt;
            font-weight: 700;
            letter-spacing: 0.25pt;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .certificate-banner {
            width: 100%;
            margin-bottom: 7mm;
            padding: 4.2mm 0 4.8mm;
            background: #1a5c3a;
            color: #ffffff;
            font-size: 36pt;
            font-weight: 800;
            letter-spacing: 6pt;
            line-height: 1;
            text-align: center;
        }

        .issuer {
            text-align: center;
        }

        .issuer-name {
            font-size: 9pt;
            font-weight: 800;
            letter-spacing: 1.2pt;
            text-transform: uppercase;
        }

        .issuer-address,
        .issuer-contact {
            font-size: 8pt;
            line-height: 1.35;
        }

        .certificate-number {
            margin-top: 6mm;
            font-size: 11pt;
            font-style: italic;
            font-weight: 800;
            text-align: center;
        }

        .small-italic {
            margin-top: 6mm;
            font-size: 9pt;
            font-style: italic;
            text-align: center;
        }

        .company-name {
            margin-top: 4mm;
            color: #172f22;
            font-size: 22pt;
            font-weight: 800;
            line-height: 1.15;
            text-align: center;
        }

        .site-address {
            width: 82%;
            margin: 2mm auto 0;
            font-size: 11pt;
            font-style: italic;
            line-height: 1.35;
            text-align: center;
        }

        .standard-title {
            margin-top: 4mm;
            color: #172f22;
            font-size: 20pt;
            font-weight: 800;
            line-height: 1.15;
            text-align: center;
        }

        .standard-subtitle {
            margin-top: 1mm;
            font-size: 10.5pt;
            font-style: italic;
            text-align: center;
        }

        .implementation-level {
            margin-top: 5mm;
            color: #172f22;
            font-size: 20pt;
            font-weight: 800;
            text-align: center;
        }

        .implementation-level-en {
            margin-top: 1mm;
            font-size: 10.5pt;
            font-style: italic;
            text-align: center;
        }

        .stars {
            margin-top: 3mm;
            color: #ffb800;
            font-family: DejaVu Sans, sans-serif;
            font-size: 29pt;
            letter-spacing: 2pt;
            line-height: 1;
            text-align: center;
        }

        .validity-text {
            width: 84%;
            margin: 9mm auto 0;
            font-size: 9pt;
            line-height: 1.45;
            text-align: justify;
        }

        .validity-period {
            margin-top: 5mm;
            font-size: 9pt;
            font-style: italic;
            line-height: 1.5;
            text-align: center;
        }

        .footer {
            position: absolute;
            left: 18mm;
            right: 18mm;
            bottom: 14mm;
            z-index: 1;
        }

        .footer-table {
            display: table;
            width: 100%;
        }

        .barcode-cell,
        .signature-cell {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }

        .barcode-box {
            width: 42mm;
            height: 19mm;
            padding: 2mm;
            border: 0.5pt solid #111827;
            background: #ffffff;
            text-align: center;
        }

        .barcode-lines {
            height: 10mm;
            margin-bottom: 1.5mm;
            background: repeating-linear-gradient(90deg, #111827 0, #111827 1px, #ffffff 1px, #ffffff 3px, #111827 3px, #111827 4px, #ffffff 4px, #ffffff 7px);
        }

        .barcode-number {
            font-size: 6.3pt;
            letter-spacing: 0.2pt;
        }

        .signature-cell {
            text-align: right;
        }

        .signature-block {
            display: inline-block;
            width: 63mm;
            text-align: center;
        }

        .signature-line {
            height: 17mm;
            border-bottom: 0.8pt solid #111827;
            margin-bottom: 2mm;
        }

        .director-name {
            font-size: 10pt;
            font-weight: 800;
        }

        .director-title,
        .company-legal {
            font-size: 9pt;
            line-height: 1.35;
        }

        .document-code {
            position: absolute;
            right: 0;
            bottom: -6mm;
            font-size: 8pt;
            font-weight: 700;
        }
    </style>
</head>
<body>
@php
    $businessProfile = $application->puUser?->businessProfile;
    $companyName = $businessProfile?->company_name ?? '-';
    $siteAddress = $site?->address ?? $application->sites?->first()?->address ?? '-';
    $starCount = match ($application->level?->value ?? $application->level) {
        'two_star' => 2,
        'three_star' => 3,
        default => 1,
    };
@endphp
<div class="page">
    <div class="watermark">SICS</div>
    <div class="revision-code">01-Rev.00</div>

    <div class="content">
        <div class="logos-row">
            <div class="logo-cell">
                <div class="logo-placeholder">IDSurvey</div>
            </div>
            <div class="logo-cell right">
                <div class="logo-placeholder">SUCOFINDO</div>
            </div>
        </div>

        <div class="sics-logo">
            <span class="sics-title">SICS</span>
            <span class="sics-subtitle">Muslim Friendly<br>Tourism</span>
        </div>

        <div class="certificate-banner">CERTIFICATE</div>

        <div class="issuer">
            <div class="issuer-name">SUCOFINDO INTERNATIONAL CERTIFICATION SERVICE</div>
            <div class="issuer-address">Graha Sucofindo B1 Floor - Jl. KH. Guru Amin Kav. 34 Jakarta 12780</div>
            <div class="issuer-contact">Phone : +62-21-7983666 ext. 1324; Email : lph@sucofindo.co.id</div>
        </div>

        <div class="certificate-number">Certificate No. {{ $certificate->certificate_number }}</div>

        <div class="small-italic">Menyatakan bahwa / Certify that</div>

        <div class="company-name">{{ $companyName }}</div>
        <div class="site-address">{{ $siteAddress }}</div>

        <div class="small-italic">Telah memenuhi / Has complied with</div>

        <div class="standard-title">Standar Pariwisata Ramah Muslim</div>
        <div class="standard-subtitle">Muslim Friendly Tourism Standard</div>

        <div class="implementation-level">Tingkat Penerapan: {{ $levelLabel }}</div>
        <div class="implementation-level-en">Implementation Level: {{ $levelEnglish }}</div>

        <div class="stars">{{ str_repeat('★', $starCount) }}</div>

        <div class="validity-text">
            Sertifikat ini berlaku dengan ketentuan bahwa organisasi selalu memenuhi kriteria sebagaimana ditetapkan oleh SUCOFINDO INTERNATIONAL CERTIFICATION SERVICES<br>
            <em>This certificate is valid provided that the organization continues to meet the criteria as laid down by SUCOFINDO INTERNATIONAL CERTIFICATION SERVICES</em>
        </div>

        <div class="validity-period">
            Sertifikat ini berlaku dari {{ $issuedAt }} sampai {{ $validUntil }}<br>
            This certificate is valid from {{ $issuedAtEn }} until {{ $validUntilEn }}
        </div>
    </div>

    <div class="footer">
        <div class="footer-table">
            <div class="barcode-cell">
                <div class="barcode-box">
                    <div class="barcode-lines"></div>
                    <div class="barcode-number">{{ $certificate->certificate_number }}</div>
                </div>
            </div>
            <div class="signature-cell">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="director-name">{{ $directorName }}</div>
                    <div class="director-title">{{ $directorTitle }}</div>
                    <div class="company-legal">PT SUCOFINDO (PERSERO)</div>
                </div>
            </div>
        </div>
        <div class="document-code">SCI-2023A</div>
    </div>
</div>
</body>
</html>
