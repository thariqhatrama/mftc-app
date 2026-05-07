<x-filament-panels::page>
    <div class="space-y-6">
        @if($invoices->isEmpty())
            <div class="text-center py-12">
                <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Tidak ada override yang perlu disetujui</h3>
            </div>
        @else
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Override Menunggu Approval ({{ $invoices->count() }})</h3>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Invoice</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Company</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Original</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">New Amount</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Selisih</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Alasan</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($invoices as $invoice)
                            @php
                                $original = (float) $invoice->original_amount;
                                $newAmt = (float) $invoice->amount;
                                $diff = $original > 0 ? abs(($newAmt - $original) / $original * 100) : 0;
                            @endphp
                            <tr class="bg-white dark:bg-gray-900">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $invoice->application?->puUser?->businessProfile?->company_name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">
                                    Rp {{ number_format($original, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($newAmt, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <x-filament::badge :color="$diff > 20 ? 'danger' : 'warning'">
                                        {{ number_format($diff, 1) }}%
                                    </x-filament::badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $invoice->override_reason }}">
                                    {{ $invoice->override_reason ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-filament::button
                                            size="sm"
                                            color="success"
                                            icon="heroicon-o-check"
                                            wire:click="approveOverride('{{ $invoice->id }}')"
                                            wire:confirm="Approve override untuk invoice {{ $invoice->invoice_number }}?"
                                        >
                                            Approve
                                        </x-filament::button>
                                        <x-filament::button
                                            size="sm"
                                            color="danger"
                                            icon="heroicon-o-x-mark"
                                            wire:click="rejectOverride('{{ $invoice->id }}')"
                                            wire:confirm="Reject override? Amount akan dikembalikan ke original."
                                        >
                                            Reject
                                        </x-filament::button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>
