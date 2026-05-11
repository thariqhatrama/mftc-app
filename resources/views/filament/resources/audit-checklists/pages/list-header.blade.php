<div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Progress Checklist Audit</p>
            <h2 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                {{ $completed }} / {{ $total }} item terisi
            </h2>
            @if($assignment)
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    {{ $assignment->application?->puUser?->businessProfile?->company_name ?? 'PU' }}
                    — {{ strtoupper(str_replace('_', ' ', $assignment->application?->status?->value ?? '-')) }}
                </p>
            @endif
        </div>

        <div class="min-w-56">
            <div class="flex items-center justify-between text-xs font-semibold text-gray-500 dark:text-gray-400">
                <span>{{ $percentage }}%</span>
                @if($isComplete)
                    <span class="text-green-600 dark:text-green-400">Checklist lengkap — laporan bisa disubmit jika semua NC closed</span>
                @else
                    <span>{{ max($total - $completed, 0) }} item belum diisi</span>
                @endif
            </div>
            <div class="mt-2 h-3 rounded-full bg-gray-100 dark:bg-gray-800">
                <div
                    class="h-3 rounded-full {{ $isComplete ? 'bg-green-600' : 'bg-amber-500' }}"
                    style="width: {{ $percentage }}%"
                ></div>
            </div>
        </div>
    </div>
</div>
