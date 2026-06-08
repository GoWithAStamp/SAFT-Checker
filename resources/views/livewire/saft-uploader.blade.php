<div>
    {{-- Upload Section --}}
    @if(!$result)
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ __('saft.upload_title') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('saft.upload_description') }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8">
            <div
                x-data="{ isDragging: false }"
                x-on:dragover.prevent="isDragging = true"
                x-on:dragleave.prevent="isDragging = false"
                x-on:drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                class="border-2 border-dashed rounded-lg p-12 text-center transition-colors"
                :class="isDragging ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'"
            >
                <input type="file" wire:model="saftFile" accept=".xml" class="hidden" x-ref="fileInput" id="saft-file-input">

                @if($fileName)
                    <svg class="mx-auto h-12 w-12 text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg font-medium text-gray-900 dark:text-white">{{ $fileName }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('saft.upload_ready') }}</p>
                @else
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-lg font-medium text-gray-700 dark:text-gray-300">{{ __('saft.upload_drag') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('saft.upload_or') }}</p>
                @endif

                <label for="saft-file-input" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 cursor-pointer transition-colors">
                    {{ $fileName ? __('saft.upload_select_another') : __('saft.upload_select') }}
                </label>
            </div>

            @if($errorMessage)
                <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-700 dark:text-red-400">{{ $errorMessage }}</p>
                </div>
            @endif

            @if($fileName)
                <div class="mt-6 flex justify-center">
                    <button wire:click="validate_saft" wire:loading.attr="disabled" class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span wire:loading.remove wire:target="validate_saft">{{ __('saft.upload_validate') }}</span>
                        <span wire:loading wire:target="validate_saft" class="flex items-center">
                            <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('saft.upload_validating') }}
                        </span>
                    </button>
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Results Section --}}
    @if($result)
    <div>
        {{-- Top Bar --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('saft.results_title') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $fileName }}</p>
            </div>
            <button wire:click="resetUpload" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ __('saft.results_new') }}
            </button>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="rounded-lg shadow-sm border p-5 {{ $summary['valid'] ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' }}">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('saft.results_status') }}</p>
                <p class="text-xl font-bold {{ $summary['valid'] ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                    {{ $summary['valid'] ? __('saft.results_valid') : __('saft.results_invalid') }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('saft.results_errors') }}</p>
                <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $summary['errors'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('saft.results_warnings') }}</p>
                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $summary['warnings'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('saft.results_info') }}</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $summary['info'] }}</p>
            </div>
        </div>

        {{-- Main Tabs --}}
        <div class="mb-6">
            <div class="flex space-x-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1 w-fit">
                <button wire:click="setActiveTab('validation')" class="px-5 py-2 text-sm font-medium rounded-md transition-colors {{ $activeTab === 'validation' ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    {{ __('saft.tab_validation') }}
                </button>
                <button wire:click="setActiveTab('data')" class="px-5 py-2 text-sm font-medium rounded-md transition-colors {{ $activeTab === 'data' ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    {{ __('saft.tab_data') }}
                </button>
            </div>
        </div>

        @if($activeTab === 'validation')
            @include('livewire.partials.validation-results')
        @endif

        @if($activeTab === 'data' && $saftData)
            @include('livewire.partials.data-tables')
        @endif
    </div>
    @endif
</div>
