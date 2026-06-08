<div>
    {{-- Upload Section --}}
    @if(!$result)
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Validar ficheiro SAFT-PT</h1>
            <p class="text-gray-600">Carregue o seu ficheiro SAFT-PT para verificar a conformidade com a legislação portuguesa.</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <div
                x-data="{ isDragging: false }"
                x-on:dragover.prevent="isDragging = true"
                x-on:dragleave.prevent="isDragging = false"
                x-on:drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                class="border-2 border-dashed rounded-lg p-12 text-center transition-colors"
                :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'"
            >
                <input
                    type="file"
                    wire:model="saftFile"
                    accept=".xml"
                    class="hidden"
                    x-ref="fileInput"
                    id="saft-file-input"
                >

                @if($fileName)
                    <svg class="mx-auto h-12 w-12 text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg font-medium text-gray-900">{{ $fileName }}</p>
                    <p class="text-sm text-gray-500 mt-1">Ficheiro pronto para validação</p>
                @else
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-lg font-medium text-gray-700">Arraste o ficheiro SAFT-PT aqui</p>
                    <p class="text-sm text-gray-500 mt-1">ou</p>
                @endif

                <label for="saft-file-input" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 cursor-pointer transition-colors">
                    {{ $fileName ? 'Escolher outro ficheiro' : 'Selecionar ficheiro XML' }}
                </label>
            </div>

            @if($errorMessage)
                <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-red-700">{{ $errorMessage }}</p>
                    </div>
                </div>
            @endif

            @if($fileName)
                <div class="mt-6 flex justify-center">
                    <button
                        wire:click="validate_saft"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span wire:loading.remove wire:target="validate_saft">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="validate_saft">
                            <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="validate_saft">Validar SAFT-PT</span>
                        <span wire:loading wire:target="validate_saft">A validar...</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Results Section --}}
    @if($result)
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Resultado da Validação</h1>
            <button
                wire:click="resetUpload"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Nova validação
            </button>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border p-6 {{ $summary['valid'] ? 'border-green-200' : 'border-red-200' }}">
                <div class="flex items-center">
                    @if($summary['valid'])
                        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Estado</p>
                        <p class="text-lg font-bold {{ $summary['valid'] ? 'text-green-700' : 'text-red-700' }}">
                            {{ $summary['valid'] ? 'Válido' : 'Com erros' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-red-100 p-6">
                <p class="text-sm font-medium text-gray-500">Erros</p>
                <p class="text-3xl font-bold text-red-600">{{ $summary['errors'] }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-yellow-100 p-6">
                <p class="text-sm font-medium text-gray-500">Avisos</p>
                <p class="text-3xl font-bold text-yellow-600">{{ $summary['warnings'] }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-blue-100 p-6">
                <p class="text-sm font-medium text-gray-500">Informações</p>
                <p class="text-3xl font-bold text-blue-600">{{ $summary['info'] }}</p>
            </div>
        </div>

        {{-- File info --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <p class="text-sm text-gray-600">
                <span class="font-medium">Ficheiro:</span> {{ $fileName }}
            </p>
        </div>

        {{-- Tab Navigation --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button wire:click="setActiveTab('all')" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Todos ({{ $summary['errors'] + $summary['warnings'] + $summary['info'] }})
                    </button>
                    @if($summary['errors'] > 0)
                    <button wire:click="setActiveTab('errors')" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'errors' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Erros ({{ $summary['errors'] }})
                    </button>
                    @endif
                    @if($summary['warnings'] > 0)
                    <button wire:click="setActiveTab('warnings')" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'warnings' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Avisos ({{ $summary['warnings'] }})
                    </button>
                    @endif
                    @if($summary['info'] > 0)
                    <button wire:click="setActiveTab('info')" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'info' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Info ({{ $summary['info'] }})
                    </button>
                    @endif
                </nav>
            </div>

            <div class="p-6">
                @if($activeTab === 'all' || $activeTab === 'errors')
                    @foreach($result['errors'] as $error)
                        <div class="flex items-start p-4 mb-3 bg-red-50 border border-red-200 rounded-lg">
                            <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-red-800">{{ $error['message'] }}</p>
                                    <span class="text-xs font-mono text-red-400 ml-2">{{ $error['code'] }}</span>
                                </div>
                                @if(!empty($error['field']))
                                    <p class="text-xs text-red-600 mt-1">Campo: {{ $error['field'] }}</p>
                                @endif
                                @if(!empty($error['details']))
                                    <p class="text-xs text-red-600 mt-1">{{ $error['details'] }}</p>
                                @endif
                                @if(!empty($error['line']))
                                    <p class="text-xs text-red-500 mt-1">Linha XML: {{ $error['line'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif

                @if($activeTab === 'all' || $activeTab === 'warnings')
                    @foreach($result['warnings'] as $warning)
                        <div class="flex items-start p-4 mb-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-yellow-800">{{ $warning['message'] }}</p>
                                    <span class="text-xs font-mono text-yellow-400 ml-2">{{ $warning['code'] }}</span>
                                </div>
                                @if(!empty($warning['field']))
                                    <p class="text-xs text-yellow-600 mt-1">Campo: {{ $warning['field'] }}</p>
                                @endif
                                @if(!empty($warning['details']))
                                    <p class="text-xs text-yellow-600 mt-1">{{ $warning['details'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif

                @if($activeTab === 'all' || $activeTab === 'info')
                    @foreach($result['info'] as $info)
                        <div class="flex items-start p-4 mb-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-blue-800">{{ $info['message'] }}</p>
                                @if(!empty($info['field']))
                                    <p class="text-xs text-blue-600 mt-1">Campo: {{ $info['field'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif

                @if(
                    ($activeTab === 'all' && empty($result['errors']) && empty($result['warnings']) && empty($result['info'])) ||
                    ($activeTab === 'errors' && empty($result['errors'])) ||
                    ($activeTab === 'warnings' && empty($result['warnings'])) ||
                    ($activeTab === 'info' && empty($result['info']))
                )
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>Sem resultados nesta categoria.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
