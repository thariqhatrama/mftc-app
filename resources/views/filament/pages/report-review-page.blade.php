<x-filament-panels::page>
    <div class="space-y-6">
        @if($applications->isEmpty())
            <div class="text-center py-12">
                <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Tidak ada laporan yang perlu direview</h3>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Application List --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Laporan Menunggu Review ({{ $applications->count() }})</h3>
                    @foreach($applications as $app)
                        <div
                            wire:click="selectApplication('{{ $app->id }}')"
                            class="cursor-pointer rounded-xl border p-4 transition
                                {{ $selectedApplicationId === $app->id
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-950'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-primary-300' }}"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ $app->puUser?->businessProfile?->company_name ?? 'N/A' }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Auditor: {{ $app->auditAssignment?->auditor?->full_name ?? '-' }}
                                    </p>
                                </div>
                                <x-filament::badge color="warning">
                                    Report Submitted
                                </x-filament::badge>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Report Summary --}}
                <div>
                    @if($selectedApplicationId && $reportSummary)
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Ringkasan Laporan</h3>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Auditor</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $reportSummary['auditor_name'] }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Audit</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $reportSummary['scheduled_date'] ?? '-' }}</p>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Checklist ({{ $reportSummary['total_checklist'] }} item)</h4>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="rounded-lg bg-green-50 dark:bg-green-950 p-3 text-center">
                                        <p class="text-2xl font-bold text-green-600">{{ $reportSummary['compliant'] }}</p>
                                        <p class="text-xs text-green-700 dark:text-green-400">Compliant</p>
                                    </div>
                                    <div class="rounded-lg bg-red-50 dark:bg-red-950 p-3 text-center">
                                        <p class="text-2xl font-bold text-red-600">{{ $reportSummary['non_compliant'] }}</p>
                                        <p class="text-xs text-red-700 dark:text-red-400">Non-Compliant</p>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-3 text-center">
                                        <p class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ $reportSummary['na'] }}</p>
                                        <p class="text-xs text-gray-700 dark:text-gray-400">N/A</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Non-Conformities ({{ $reportSummary['total_nc'] }})</h4>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-lg bg-amber-50 dark:bg-amber-950 p-3 text-center">
                                        <p class="text-2xl font-bold text-amber-600">{{ $reportSummary['nc_open'] }}</p>
                                        <p class="text-xs text-amber-700 dark:text-amber-400">Open</p>
                                    </div>
                                    <div class="rounded-lg bg-green-50 dark:bg-green-950 p-3 text-center">
                                        <p class="text-2xl font-bold text-green-600">{{ $reportSummary['nc_closed'] }}</p>
                                        <p class="text-xs text-green-700 dark:text-green-400">Closed</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-2">
                                {{ $this->approveAction }}
                                {{ $this->rejectAction }}
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-12 text-center">
                            <x-heroicon-o-document-magnifying-glass class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Pilih aplikasi untuk melihat ringkasan laporan</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
