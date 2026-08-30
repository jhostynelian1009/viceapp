<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles de la Planificación') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Botón de Volver Atrás (Superior) -->
            <div class="mb-6">
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver Atrás
                </a>
            </div>

            <!-- Card de Detalles de la Planificación -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ $planning->title }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <span class="font-bold text-gray-600">Estado:</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @switch($planning->status)
                                    @case('borrador') bg-yellow-100 text-yellow-800 @break
                                    @case('revisión') bg-blue-100 text-blue-800 @break
                                    @case('aprobado') bg-green-100 text-green-800 @break
                                    @case('rechazado') bg-red-100 text-red-800 @break
                                @endswitch">
                                {{ ucfirst($planning->status) }}
                            </span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-600">Fecha de Subida:</span>
                            <span>{{ $planning->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div>
                            <a href="{{ route('plannings.download', $planning) }}" class="text-blue-500 hover:text-blue-700 font-semibold">
                                <svg class="inline-block w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Descargar Archivo
                            </a>
                        </div>
                    </div>

                    <!-- Visor de Documentos Híbrido -->
                    @if($planning->file_path)
                        <div class="mt-6 border-t pt-6">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-xl font-bold text-gray-800">Visualizador de Documento</h4>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-600">Visor Integrado</span>
                                    <label for="viewer-toggle" class="inline-flex relative items-center cursor-pointer">
                                        <input type="checkbox" id="viewer-toggle" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:bg-blue-600"></div>
                                        <span class="ml-3 text-sm font-medium text-gray-900">Google Docs</span>
                                    </label>
                                </div>
                            </div>

                            @php
                                $fileExtension = strtolower(pathinfo($planning->file_path, PATHINFO_EXTENSION));
                            @endphp

                            @if($fileExtension === 'pdf')
                                <div class="bg-gray-100 p-2 rounded-lg">
                                    <iframe src="{{ Storage::url($planning->file_path) }}" style="width:100%; height:700px;" frameborder="0"></iframe>
                                </div>
                            @elseif(in_array($fileExtension, ['doc', 'docx']))
                                <div id="mammoth-viewer">
                                    <div id="loading-indicator" class="text-center p-8">
                                        <p class="text-lg font-semibold mb-2">Cargando vista previa...</p>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div id="progress-bar" class="bg-blue-600 h-2.5 rounded-full w-0 transition-all duration-500 ease-linear"></div>
                                        </div>
                                    </div>
                                    <div id="word-content" class="bg-gray-50 p-4 rounded-lg prose max-w-none border hidden"></div>
                                </div>
                                <div id="google-viewer" class="hidden">
                                    <div class="bg-gray-100 p-2 rounded-lg">
                                        <iframe src="https://docs.google.com/gview?url={{ url(Storage::url($planning->file_path)) }}&embedded=true" style="width:100%; height:700px;" frameborder="0"></iframe>
                                    </div>
                                </div>
                            @else
                                <div class="bg-gray-100 text-gray-600 p-4 rounded-lg">
                                    <p>No hay una vista previa disponible para este tipo de archivo.</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="mt-6 text-center text-gray-500">
                            <p>No hay un archivo asociado a esta planificación.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Panel de Gestión de Estado y Comentarios -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h4 class="text-2xl font-bold text-gray-900 mb-6">Comentarios</h4>
                            <div class="mb-8">
                                <form action="{{ route('comments.store', $planning) }}" method="POST">
                                    @csrf
                                    <textarea name="body" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Escribe tu comentario aquí..."></textarea>
                                    <div class="mt-4 text-right">
                                        <x-primary-button>Enviar Comentario</x-primary-button>
                                    </div>
                                </form>
                            </div>
                            <div class="space-y-6">
                                @forelse ($planning->comments as $comment)
                                    <div class="bg-gray-50 p-4 rounded-lg shadow">
                                        <p class="font-bold">{{ $comment->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                                        <p class="mt-2">{{ $comment->body }}</p>
                                    </div>
                                @empty
                                    <p class="text-gray-500">Aún no hay comentarios.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                @auth
                    @if( (Auth::user()->hasRole('secretaria') || Auth::user()->hasRole('vicerrectorado')) && $planning->status === 'revisión' )
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-blue-500">
                            <div class="p-6">
                                <h4 class="text-xl font-bold text-gray-800 mb-4">Panel de Gestión</h4>
                                <div class="space-y-4">
                                    <form action="{{ route('plannings.updateStatus', $planning) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="aprobado">
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 bg-green-600 border rounded-md font-semibold text-white hover:bg-green-700">
                                            Aprobar
                                        </button>
                                    </form>
                                    <form action="{{ route('plannings.updateStatus', $planning) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rechazado">
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 bg-red-600 border rounded-md font-semibold text-white hover:bg-red-700">
                                            Rechazar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>

            <!-- Botón de Volver Atrás (Inferior) -->
            <div class="mt-8 text-center">
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver Atrás
                </a>
            </div>

        </div>
    </div>

    @once
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js"></script>
        <script>
            if (document.getElementById('mammoth-viewer')) {
                document.addEventListener('DOMContentLoaded', () => {
                    const mammothViewer = document.getElementById('mammoth-viewer');
                    const googleViewer = document.getElementById('google-viewer');
                    const toggle = document.getElementById('viewer-toggle');

                    toggle.addEventListener('change', () => {
                        mammothViewer.classList.toggle('hidden');
                        googleViewer.classList.toggle('hidden');
                    });

                    const wordContent = document.getElementById('word-content');
                    const loadingIndicator = document.getElementById('loading-indicator');
                    const progressBar = document.getElementById('progress-bar');
                    const url = "{{ route('plannings.download', $planning) }}";

                    const loadDocument = async () => {
                        try {
                            const response = await fetch(url);
                            if (!response.ok) throw new Error('Error de red');

                            const reader = response.body.getReader();
                            const contentLength = +response.headers.get('Content-Length');
                            let receivedLength = 0;
                            let chunks = [];

                            while(true) {
                                const {done, value} = await reader.read();
                                if (done) break;
                                chunks.push(value);
                                receivedLength += value.length;
                                const progress = (receivedLength / contentLength) * 100;
                                progressBar.style.width = `${progress}%`;
                            }

                            let chunksAll = new Uint8Array(receivedLength);
                            let position = 0;
                            for(let chunk of chunks) {
                                chunksAll.set(chunk, position);
                                position += chunk.length;
                            }

                            const result = await mammoth.convertToHtml({ arrayBuffer: chunksAll.buffer });
                            wordContent.innerHTML = result.value;
                            wordContent.classList.remove('hidden');

                        } catch (error) {
                            wordContent.innerHTML = `<div class="text-red-500 p-4"><b>Error:</b> ${error.message}</div>`;
                            wordContent.classList.remove('hidden');
                        } finally {
                            loadingIndicator.classList.add('hidden');
                        }
                    }
                    loadDocument();
                });
            }
        </script>
        @endpush
    @endonce
</x-app-layout>
