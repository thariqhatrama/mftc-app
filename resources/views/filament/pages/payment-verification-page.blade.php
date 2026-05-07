<x-filament-panels::page>
    <div class="space-y-6">
        @if($invoices->isEmpty())
            <div class="text-center py-12">
                <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Tidak ada pembayaran yang perlu diverifikasi</h3>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Invoice List --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Invoices Menunggu Verifikasi ({{ $invoices->count() }})</h3>
                    @foreach($invoices as $invoice)
                        <div
                            wire:click="previewProof('{{ $invoice->id }}')"
                            class="cursor-pointer rounded-xl border p-4 transition
                                {{ $selectedInvoiceId === $invoice->id
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-950'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-primary-300' }}"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $invoice->application?->puUser?->businessProfile?->company_name ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $invoice->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Preview Panel --}}
                <div>
                    @if($selectedInvoiceId && $previewUrl)
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bukti Pembayaran</h3>

                            @php
                                $ext = strtolower(pathinfo($previewUrl, PATHINFO_EXTENSION));
                                $isPdf = str_contains($previewUrl, '.pdf');
                            @endphp

                            @if($isPdf)
                                <iframe src="{{ $previewUrl }}" class="w-full h-96 rounded-lg border"></iframe>
                            @else
                                <img src="{{ $previewUrl }}" alt="Bukti Bayar" class="w-full rounded-lg border max-h-96 object-contain" />
                            @endif

                            <div class="flex gap-3">
                                {{ $this->verifyAction }}
                                {{ $this->rejectAction }}
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-12 text-center">
                            <x-heroicon-o-photo class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Pilih invoice untuk melihat bukti pembayaran</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
