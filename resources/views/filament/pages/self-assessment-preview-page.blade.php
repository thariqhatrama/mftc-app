<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            {{ $this->form }}
        </div>

        {{-- Preview --}}
        @if($questions->isEmpty())
            <div class="text-center py-12">
                <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                    @if(!$filterScope && !$filterLevel)
                        Pilih scope dan level untuk melihat preview
                    @else
                        Tidak ada pertanyaan aktif untuk filter ini
                    @endif
                </h3>
            </div>
        @else
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Preview Self-Assessment ({{ $questions->count() }} pertanyaan)
                    </h3>
                    <x-filament::badge color="gray">Mode Preview</x-filament::badge>
                </div>

                @php $currentCategory = null; @endphp

                @foreach($questions as $question)
                    @if($question->category !== $currentCategory)
                        @php $currentCategory = $question->category; @endphp
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-2 pt-4">
                            <h4 class="text-sm font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">
                                {{ $currentCategory }}
                            </h4>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <div class="flex items-start gap-2">
                            <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded px-2 py-0.5 mt-0.5 font-mono">
                                {{ $question->sort_order }}
                            </span>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $question->question_text }}
                                    @if($question->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                @if($question->helper_text)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $question->helper_text }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="ml-10">
                            @switch($question->input_type)
                                @case('text')
                                    <input type="text" disabled placeholder="Jawaban teks..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-400 cursor-not-allowed" />
                                    @break
                                @case('textarea')
                                    <textarea disabled rows="3" placeholder="Jawaban panjang..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-400 cursor-not-allowed"></textarea>
                                    @break
                                @case('number')
                                    <input type="number" disabled placeholder="0" class="w-40 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-400 cursor-not-allowed" />
                                    @break
                                @case('radio')
                                    @if(is_array($question->input_options))
                                        <div class="space-y-2">
                                            @foreach($question->input_options as $opt)
                                                <label class="flex items-center gap-2 text-sm text-gray-400 cursor-not-allowed">
                                                    <input type="radio" disabled class="text-gray-300" />
                                                    {{ $opt['label'] ?? $opt['value'] ?? $opt }}
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                    @break
                                @case('checkbox')
                                    @if(is_array($question->input_options))
                                        <div class="space-y-2">
                                            @foreach($question->input_options as $opt)
                                                <label class="flex items-center gap-2 text-sm text-gray-400 cursor-not-allowed">
                                                    <input type="checkbox" disabled class="text-gray-300 rounded" />
                                                    {{ $opt['label'] ?? $opt['value'] ?? $opt }}
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                    @break
                                @case('select')
                                    <select disabled class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                                        <option>-- Pilih --</option>
                                        @if(is_array($question->input_options))
                                            @foreach($question->input_options as $opt)
                                                <option>{{ $opt['label'] ?? $opt['value'] ?? $opt }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @break
                                @case('file')
                                    <div class="flex items-center gap-2 text-sm text-gray-400">
                                        <x-heroicon-o-arrow-up-tray class="h-5 w-5" />
                                        <span>Upload file (disabled)</span>
                                    </div>
                                    @break
                                @default
                                    <input type="text" disabled placeholder="..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-400 cursor-not-allowed" />
                            @endswitch
                        </div>
                    </div>
                @endforeach

                {{-- Disabled submit button --}}
                <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        disabled
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-300 dark:bg-gray-700 px-6 py-2.5 text-sm font-semibold text-gray-500 dark:text-gray-400 cursor-not-allowed"
                        title="Mode Preview — tombol ini tidak aktif"
                    >
                        <x-heroicon-o-paper-airplane class="h-5 w-5" />
                        Submit Self-Assessment
                    </button>
                    <p class="text-xs text-gray-400 mt-1">Mode Preview — tombol ini tidak aktif</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
