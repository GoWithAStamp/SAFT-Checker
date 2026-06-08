<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6 space-y-3">
        @if(empty($result['errors']) && empty($result['warnings']) && empty($result['info']))
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-green-300 dark:text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-lg font-medium text-green-600 dark:text-green-400">{{ __('saft.results_no_issues') }}</p>
            </div>
        @endif

        @foreach($result['errors'] as $error)
            <div class="flex items-start p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold text-red-800 dark:text-red-300">{{ $error['message'] }}</p>
                        <span class="text-xs font-mono text-red-400 dark:text-red-500 whitespace-nowrap">{{ $error['code'] }}</span>
                    </div>
                    @if(!empty($error['field']))
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ __('saft.results_field') }}: <span class="font-mono">{{ $error['field'] }}</span></p>
                    @endif
                    @if(!empty($error['details']))
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $error['details'] }}</p>
                    @endif
                    @if(!empty($error['line']))
                        <p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ __('saft.results_xml_line') }}: {{ $error['line'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach

        @foreach($result['warnings'] as $warning)
            <div class="flex items-start p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">{{ $warning['message'] }}</p>
                        <span class="text-xs font-mono text-yellow-400 dark:text-yellow-500 whitespace-nowrap">{{ $warning['code'] }}</span>
                    </div>
                    @if(!empty($warning['field']))
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">{{ __('saft.results_field') }}: <span class="font-mono">{{ $warning['field'] }}</span></p>
                    @endif
                    @if(!empty($warning['details']))
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">{{ $warning['details'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach

        @foreach($result['info'] as $info)
            <div class="flex items-start p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">{{ $info['message'] }}</p>
                    @if(!empty($info['field']))
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">{{ __('saft.results_field') }}: <span class="font-mono">{{ $info['field'] }}</span></p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
