<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <p class="text-3xl font-bold {{ $totalBreached > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $totalBreached }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total SLA Breached</p>
            </div>
            @foreach($slaGroups as $key => $group)
                @if($group['total'] > 0)
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $group['total'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $group['label'] }}</p>
                        @if($group['breached'] > 0)
                            <x-filament::badge color="danger" class="mt-1">{{ $group['breached'] }} overdue</x-filament::badge>
                        @else
                            <x-filament::badge color="success" class="mt-1">On track</x-filament::badge>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>

        {{-- SLA Groups --}}
        @foreach($slaGroups as $key => $group)
            @if($group['total'] > 0)
                <div class="rounded-xl border {{ $group['breached'] > 0 ? 'border-red-300 dark:border-red-800' : 'border-gray-200 dark:border-gray-700' }} overflow-hidden">
                    <div class="px-4 py-3 {{ $group['breached'] > 0 ? 'bg-red-50 dark:bg-red-950' : 'bg-gray-50 dark:bg-gray-800' }} flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $group['label'] }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $group['description'] }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $group['total'] }} item</span>
                            @if($group['breached'] > 0)
                                <x-filament::badge color="danger">{{ $group['breached'] }} overdue</x-filament::badge>
                            @endif
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-white dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Company</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Scope</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Masuk Status</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hari</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">SLA</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($group['items'] as $item)
                                    <tr class="{{ $item['breached'] ? 'bg-red-50 dark:bg-red-950/30' : 'bg-white dark:bg-gray-900' }}">
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ $item['company'] }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $item['scope'] }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $item['entered_at'] ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-center font-semibold {{ $item['breached'] ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                                            {{ $item['days_since'] }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-center text-gray-500 dark:text-gray-400">
                                            ≤{{ $item['sla_days'] }}
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            @if($item['breached'])
                                                <x-filament::badge color="danger">Overdue</x-filament::badge>
                                            @else
                                                @php
                                                    $pct = $item['sla_days'] > 0 ? ($item['days_since'] / $item['sla_days']) * 100 : 0;
                                                @endphp
                                                @if($pct >= 75)
                                                    <x-filament::badge color="warning">Warning</x-filament::badge>
                                                @else
                                                    <x-filament::badge color="success">OK</x-filament::badge>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Empty State --}}
        @if(collect($slaGroups)->sum('total') === 0)
            <div class="text-center py-12">
                <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-green-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Semua SLA terpenuhi</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada aplikasi yang menunggu tindakan.</p>
            </div>
        @endif

        {{-- Refresh --}}
        <div class="text-right">
            <x-filament::button color="gray" icon="heroicon-o-arrow-path" wire:click="loadSlaData" size="sm">
                Refresh
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
