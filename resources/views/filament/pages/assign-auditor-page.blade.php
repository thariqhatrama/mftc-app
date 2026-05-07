<x-filament-panels::page>
    <div class="space-y-6">
        @if($applications->isEmpty())
            <div class="text-center py-12">
                <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Tidak ada aplikasi yang menunggu penugasan auditor</h3>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Application List --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Aplikasi Siap Audit ({{ $applications->count() }})</h3>
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
                                        {{ ucwords(str_replace('_', ' ', $app->scope?->value ?? '-')) }}
                                        &bull;
                                        {{ ucwords(str_replace('_', ' ', $app->level?->value ?? '-')) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <x-filament::badge color="info">
                                        Audit Ready
                                    </x-filament::badge>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Assignment Form --}}
                <div>
                    @if($selectedApplicationId)
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Assign Auditor</h3>

                            <form wire:submit="assign">
                                {{ $this->form }}

                                <div class="mt-4">
                                    <x-filament::button type="submit" color="primary" icon="heroicon-o-user-plus">
                                        Tugaskan Auditor
                                    </x-filament::button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-12 text-center">
                            <x-heroicon-o-user-plus class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Pilih aplikasi untuk menugaskan auditor</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
