<div class="space-y-4 p-2">
    <div class="space-y-2 rounded-lg bg-gray-50 p-3 text-sm">
        <div>
            <span class="text-xs font-semibold uppercase text-gray-500">Deskripsi NC</span>
            <p class="mt-0.5 text-gray-800">{{ $nc->description }}</p>
        </div>
        @if ($nc->pu_correction)
            <div>
                <span class="text-xs font-semibold uppercase text-gray-500">Perbaikan PU</span>
                <p class="mt-0.5 text-gray-800">{{ $nc->pu_correction }}</p>
            </div>
        @endif
    </div>

    @if ($nc->pu_correction_attachment_url)
        @php
            $url = route('nc.attachment.download', $nc->id);
            $ext = strtolower(pathinfo($nc->pu_correction_attachment_url, PATHINFO_EXTENSION));
        @endphp

        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true))
            <img src="{{ $url }}" alt="File Perbaikan" class="max-h-96 max-w-full rounded-lg object-contain shadow" />
        @elseif ($ext === 'pdf')
            <iframe src="{{ $url }}" class="w-full rounded-lg border" style="height: 400px;"></iframe>
        @endif

        <a
            href="{{ $url }}"
            target="_blank"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-800"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Buka / Download File
        </a>
    @else
        <p class="py-4 text-center text-gray-400">Belum ada file lampiran</p>
    @endif
</div>
