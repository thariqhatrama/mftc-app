<div class="flex flex-col items-center gap-4 p-2">
    <div class="w-full rounded-lg bg-gray-50 p-3 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-500">Invoice</span>
            <span class="font-semibold">{{ $invoice->invoice_number }}</span>
        </div>
        <div class="mt-1 flex justify-between">
            <span class="text-gray-500">Jumlah</span>
            <span class="font-semibold text-emerald-700">
                Rp {{ number_format($invoice->amount, 0, ',', '.') }}
            </span>
        </div>
    </div>

    @if ($invoice->payment_proof_url)
        @php
            $url = route('invoice.proof.view', $invoice->id);
            $ext = strtolower(pathinfo($invoice->payment_proof_url, PATHINFO_EXTENSION));
        @endphp

        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true))
            <img
                src="{{ $url }}"
                alt="Bukti Pembayaran"
                class="max-h-[500px] max-w-full rounded-lg object-contain shadow-md"
            />
        @elseif ($ext === 'pdf')
            <iframe
                src="{{ $url }}"
                class="w-full rounded-lg border"
                style="height: 500px;"
            ></iframe>
        @else
            <a
                href="{{ $url }}"
                target="_blank"
                class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
            >
                Download File
            </a>
        @endif

        <a href="{{ $url }}" target="_blank" class="text-xs text-emerald-700 hover:underline">
            Buka di tab baru ↗
        </a>
    @else
        <div class="py-8 text-center text-gray-400">
            <x-heroicon-o-photo class="mx-auto mb-2 h-12 w-12 opacity-30" />
            <p>Belum ada bukti bayar</p>
        </div>
    @endif
</div>
