<x-filament-panels::page>
    {{-- Banner: Mode Tampilan Super Admin --}}
    <div class="rounded-xl bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 p-4 flex items-start justify-between gap-4">
        <div>
            <p class="font-semibold text-blue-900 dark:text-blue-100">
                Mode Tampilan Super Admin
            </p>
            <p class="text-sm text-blue-800 dark:text-blue-200 mt-1">
                Anda sedang melihat dashboard <strong>{{ $puUser?->full_name }}</strong>
                ({{ $puUser?->email }}). Setiap aksi yang Anda lakukan akan tercatat di audit log
                sebagai <code class="text-xs bg-blue-100 dark:bg-blue-900 px-1.5 py-0.5 rounded">pu_action_via_admin</code>.
            </p>
        </div>
        <a href="/admin/users"
           class="shrink-0 inline-flex items-center gap-1 text-sm font-medium text-blue-700 dark:text-blue-200 hover:underline">
            ← Kembali ke Daftar User
        </a>
    </div>

    {{-- Tab Navigation --}}
    @php
        $tabs = [
            'ringkasan' => 'Ringkasan',
            'pengajuan' => 'Pengajuan',
            'self_assessment' => 'Self-Assessment',
            'sertifikat' => 'Sertifikat',
        ];
    @endphp

    <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-2">
        @foreach($tabs as $key => $label)
            <button
                type="button"
                wire:click="setTab('{{ $key }}')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors
                    {{ $activeTab === $key
                        ? 'bg-primary-600 text-white'
                        : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tab 1 — Ringkasan --}}
    @if($activeTab === 'ringkasan')
        @php($profile = $puUser?->businessProfile)
        @php($stats = $this->stats)

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Profil Usaha</h3>
            @if($profile)
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Nama Perusahaan</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $profile->company_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">NIB</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $profile->nib ?? '—' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-gray-500 dark:text-gray-400">Alamat</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $profile->address ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Contact Person</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $profile->contact_person ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Contact Phone</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $profile->contact_phone ?? '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Profil usaha belum diisi.</p>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Pengajuan</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <p class="text-3xl font-bold text-green-600">{{ $stats['certified'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Sertifikat Terbit</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['active'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Pengajuan Aktif</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <p class="text-3xl font-bold text-gray-500">{{ $stats['draft'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Draft</p>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 font-semibold text-gray-900 dark:text-white">
                Pengajuan Terbaru
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900 text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Scope</th>
                            <th class="px-4 py-2">Level</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($this->applications->take(5) as $app)
                            <tr>
                                <td class="px-4 py-2 font-mono text-xs">{{ \Illuminate\Support\Str::limit($app->id, 8, '') }}</td>
                                <td class="px-4 py-2">{{ ucwords(str_replace('_', ' ', $app->scope?->value ?? '-')) }}</td>
                                <td class="px-4 py-2">{{ ucwords(str_replace('_', ' ', $app->level?->value ?? '-')) }}</td>
                                <td class="px-4 py-2">
                                    <x-filament::badge color="info">{{ strtoupper(str_replace('_', ' ', $app->status->value)) }}</x-filament::badge>
                                </td>
                                <td class="px-4 py-2 text-gray-500">{{ $app->created_at?->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada pengajuan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Tab 2 — Pengajuan --}}
    @if($activeTab === 'pengajuan')
        <div class="space-y-4">
            @forelse($this->applications as $app)
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-5 space-y-3">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-mono text-xs text-gray-500">{{ $app->id }}</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ ucwords(str_replace('_', ' ', $app->scope?->value ?? '-')) }}
                                — {{ ucwords(str_replace('_', ' ', $app->level?->value ?? '-')) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Dibuat: {{ $app->created_at?->format('d M Y H:i') }}</p>
                        </div>
                        <x-filament::badge color="info">{{ strtoupper(str_replace('_', ' ', $app->status->value)) }}</x-filament::badge>
                    </div>

                    {{-- Aksi per status --}}
                    @if($app->status->value === 'invoiced' && $app->invoice)
                        <div class="bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-800 rounded-lg p-3 text-sm">
                            <p class="font-medium text-amber-900 dark:text-amber-100">Invoice {{ $app->invoice->invoice_number }}</p>
                            <p class="text-amber-800 dark:text-amber-200">Jumlah: Rp {{ number_format((float) $app->invoice->amount, 0, ',', '.') }}</p>
                            <button
                                type="button"
                                wire:click="markPaymentUploaded('{{ $app->id }}')"
                                wire:confirm="Tandai bukti pembayaran sebagai terupload? Aksi ini akan dicatat di audit log."
                                class="mt-3 inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg">
                                Tandai Bukti Bayar Terupload
                            </button>
                        </div>
                    @endif

                    @if($app->status->value === 'auditor_assigned' && $app->auditAssignment)
                        <div class="bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-lg p-3 text-sm space-y-1">
                            <p class="font-medium text-blue-900 dark:text-blue-100">Penugasan Auditor</p>
                            <p class="text-blue-800 dark:text-blue-200">
                                Tanggal: {{ optional($app->auditAssignment->scheduled_date)->format('d M Y') }}
                                {{ $app->auditAssignment->scheduled_time ?? '' }}
                            </p>
                            <p class="text-blue-800 dark:text-blue-200">Lokasi: {{ $app->auditAssignment->location ?? '—' }}</p>
                            <button
                                type="button"
                                wire:click="confirmSchedule('{{ $app->id }}')"
                                wire:confirm="Konfirmasi jadwal audit ini? Aksi ini akan dicatat di audit log."
                                class="mt-2 inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg">
                                Konfirmasi Jadwal
                            </button>
                        </div>
                    @endif

                    @if($app->status->value === 'revision')
                        @php($ncs = $this->getRevisionsForApplication($app->id))
                        <div class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 rounded-lg p-3 text-sm space-y-2">
                            <p class="font-medium text-red-900 dark:text-red-100">{{ $ncs->count() }} Non-Conformity Aktif</p>
                            <ul class="list-disc list-inside text-red-800 dark:text-red-200 space-y-1">
                                @foreach($ncs as $nc)
                                    <li>
                                        <span class="font-medium">[{{ strtoupper($nc->severity) }}]</span>
                                        {{ \Illuminate\Support\Str::limit($nc->description, 80) }}
                                        @if($nc->corrective_action_deadline)
                                            — deadline: {{ $nc->corrective_action_deadline->format('d M Y') }}
                                        @endif
                                        @if($nc->verified_by_auditor)
                                            <x-filament::badge color="success">Diverifikasi</x-filament::badge>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                            <p class="text-xs text-red-700 dark:text-red-300 italic">
                                Form perbaikan harus diisi langsung oleh PU. Super Admin hanya dapat melihat status NC.
                            </p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-8 text-center text-gray-500">
                    Belum ada pengajuan.
                </div>
            @endforelse
        </div>
    @endif

    {{-- Tab 3 — Self-Assessment --}}
    @if($activeTab === 'self_assessment')
        <div class="space-y-4">
            @forelse($this->assessmentData as $app)
                @php($assessment = $app->selfAssessment)
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-5 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-mono text-xs text-gray-500">{{ $app->id }}</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ ucwords(str_replace('_', ' ', $app->scope?->value ?? '-')) }}
                                / {{ ucwords(str_replace('_', ' ', $app->level?->value ?? '-')) }}
                            </p>
                        </div>
                        @if($assessment?->submitted_at)
                            <x-filament::badge color="success">Submitted {{ $assessment->submitted_at->format('d M Y') }}</x-filament::badge>
                        @else
                            <x-filament::badge color="warning">Draft</x-filament::badge>
                        @endif
                    </div>

                    <div class="space-y-2">
                        @forelse($assessment?->answers ?? [] as $answer)
                            <div class="border-l-4 border-primary-200 dark:border-primary-800 pl-3 py-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $answer->question?->question_text ?? 'Pertanyaan dihapus' }}</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Jawaban: <span class="font-mono">{{ $answer->answer_value ?? '—' }}</span>
                                </p>
                                @if(! empty($answer->answer_files))
                                    <p class="text-xs text-gray-500">{{ count($answer->answer_files) }} file lampiran</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada jawaban.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-8 text-center text-gray-500">
                    Belum ada pengajuan dengan self-assessment.
                </div>
            @endforelse
        </div>
    @endif

    {{-- Tab 4 — Sertifikat --}}
    @if($activeTab === 'sertifikat')
        <div class="space-y-3">
            @forelse($this->certificates as $cert)
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-5 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $cert->certificate_number }}</p>
                        <p class="text-sm text-gray-500">
                            Level: {{ ucwords(str_replace('_', ' ', $cert->level?->value ?? '-')) }}
                            • Issued: {{ $cert->issued_at?->format('d M Y') }}
                            • Valid Until: {{ $cert->valid_until?->format('d M Y') }}
                        </p>
                    </div>
                    @if($cert->certificate_pdf_url)
                        <a href="{{ route('api.certificates.download', ['id' => $cert->id]) }}"
                           target="_blank"
                           class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg">
                            Download PDF
                        </a>
                    @else
                        <x-filament::badge color="warning">PDF belum tersedia</x-filament::badge>
                    @endif
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-8 text-center text-gray-500">
                    Belum ada sertifikat aktif.
                </div>
            @endforelse
        </div>
    @endif
</x-filament-panels::page>
