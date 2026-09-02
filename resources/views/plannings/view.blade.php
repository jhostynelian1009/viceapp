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

                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <span class="font-bold text-gray-600">Estado:</span>
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                @switch($planning->status?->value ?? $planning->status)
                                    @case('draft') bg-yellow-100 text-yellow-800 @break
                                    @case('pending') bg-blue-100 text-blue-800 @break
                                    @case('approved') bg-green-100 text-green-800 @break
                                    @case('rejected') bg-red-100 text-red-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch">
                                {{ $planning->status instanceof \App\Enums\PlanningStatus ? $planning->status->label() : ucfirst($planning->status) }}
                            </span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-600">Fecha de Subida:</span>
                            <span>{{ $planning->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div>
                            <a href="{{ route('plannings.download', $planning) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                <svg class="inline-block w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Descargar Versión Actual
                            </a>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg mb-6 border">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="font-bold text-gray-700 block">{{ __('Clasificación Académica') }}:</span>
                                <span class="text-gray-900 text-base font-semibold">
                                    @if($planning->assignment)
                                        {{ $planning->assignment->subject ? ($planning->assignment->subject->academicArea ? $planning->assignment->subject->academicArea->name : __('Sin Área')) . ' — ' . $planning->assignment->subject->name : __('Sin Asignatura') }}
                                        <span class="text-sm font-normal text-gray-600">
                                            (Curso: {{ $planning->assignment->course ? $planning->assignment->course->name : __('Sin Curso') }} — Paralelo: {{ $planning->assignment->parallel ? $planning->assignment->parallel->name : __('Sin Paralelo') }})
                                        </span>
                                    @else
                                        {{ $planning->subject->name ?? 'N/A' }}
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-700 block">{{ __('Semana Planificada') }}:</span>
                                <span class="text-gray-900 text-base font-semibold">
                                    @if($planning->week_start && $planning->week_end)
                                        {{ $planning->week_start->format('d/m/Y') }} al {{ $planning->week_end->format('d/m/Y') }}
                                    @else
                                        <span class="text-gray-400 italic">{{ __('No asignada') }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de Versiones e Inspección de Documentos -->
                    @if($planning->versions->count() > 0)
                        <div class="mt-6 border-t pt-6">
                            <h4 class="text-xl font-bold text-gray-800 mb-4">Historial de Versiones del Documento</h4>
                            <div class="overflow-x-auto border rounded-lg mb-6">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Versión</th>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre Original</th>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tamaño</th>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cargado por</th>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($planning->versions as $ver)
                                            <tr class="hover:bg-gray-50 @if($planning->current_version_id == $ver->id) bg-blue-50/50 @endif">
                                                <td class="px-4 py-2 text-sm font-bold text-gray-800">
                                                    v{{ $ver->version }}
                                                    @if($planning->current_version_id == $ver->id)
                                                        <span class="ml-2 text-xs text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full font-semibold">Actual</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-700">{{ $ver->original_name }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-500">{{ number_format($ver->size / 1024, 1) }} KB</td>
                                                <td class="px-4 py-2 text-sm text-gray-600">{{ $ver->uploader?->name ?? 'N/A' }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-500">{{ $ver->created_at->format('d/m/Y H:i') }}</td>
                                                <td class="px-4 py-2 text-sm text-right">
                                                    @can('download', $planning)
                                                        <a href="{{ route('plannings.versions.download', [$planning, $ver]) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-xs">Descargar</a>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Visor de Documentos Híbrido -->
                    @if($planning->file_path)
                        <div class="mt-6 border-t pt-6">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-xl font-bold text-gray-800">Visualizador de Documento Actual</h4>
                                <span class="text-sm text-gray-600">Almacenamiento Privado Seguro</span>
                            </div>

                            @php
                                $fileExtension = strtolower(pathinfo($planning->file_path, PATHINFO_EXTENSION));
                            @endphp

                            @if($fileExtension === 'pdf')
                                <div class="bg-gray-100 p-2 rounded-lg">
                                    <iframe src="{{ route('plannings.preview', $planning) }}" style="width:100%; height:700px;" frameborder="0"></iframe>
                                </div>
                            @elseif(in_array($fileExtension, ['doc', 'docx']))
                                <div id="mammoth-viewer">
                                    <div id="loading-indicator" class="text-center p-8">
                                        <p class="text-lg font-semibold mb-2">Cargando vista previa local...</p>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div id="progress-bar" class="bg-blue-600 h-2.5 rounded-full w-0 transition-all duration-500 ease-linear"></div>
                                        </div>
                                    </div>
                                    <div id="word-content" class="bg-gray-50 p-4 rounded-lg prose max-w-none border hidden"></div>
                                </div>
                            @else
                                <div class="bg-gray-100 text-gray-600 p-4 rounded-lg">
                                    <p>No hay una vista previa disponible para este tipo de archivo.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sección de Revisiones e Historial de Decisiones -->
            @if($planning->reviews->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6">
                        <h4 class="text-xl font-bold text-gray-900 mb-4">Historial de Revisiones del Vicerrectorado</h4>
                        <div class="space-y-4">
                            @foreach($planning->reviews as $rev)
                                <div class="p-4 rounded-lg border @if($rev->decision === 'approved') bg-green-50 border-green-200 @else bg-red-50 border-red-200 @endif">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-bold text-sm @if($rev->decision === 'approved') text-green-800 @else text-red-800 @endif">
                                            Decisión: {{ $rev->decision === 'approved' ? 'Aprobada' : 'Rechazada' }} (v{{ $rev->version?->version ?? 1 }})
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $rev->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700"><strong>Revisor:</strong> {{ $rev->reviewer?->name ?? 'Vicerrectorado' }}</p>
                                    @if($rev->comment)
                                        <p class="text-sm text-gray-800 mt-2 bg-white p-3 rounded border"><strong>Motivo / Observación:</strong> {{ $rev->comment }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

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
                    {{-- EXCLUSIVO VICERRECTORADO: Solo Vicerrectorado puede aprobar o rechazar --}}
                    @if( Auth::user()->hasRole('vicerrectorado') && ($planning->status === \App\Enums\PlanningStatus::PENDING || $planning->status === 'pending') )
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-blue-500">
                            <div class="p-6">
                                <h4 class="text-xl font-bold text-gray-800 mb-4">Panel de Decisión (Vicerrectorado)</h4>
                                <div class="space-y-6">
                                    <!-- Formulario Aprobar -->
                                    <form action="{{ route('plannings.approve', $planning) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 bg-green-600 border rounded-md font-semibold text-white hover:bg-green-700">
                                            Aprobar Planificación
                                        </button>
                                    </form>

                                    <!-- Formulario Rechazar con Motivo Obligatorio -->
                                    <form action="{{ route('plannings.reject', $planning) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label for="comment" class="block text-sm font-medium text-gray-700">Motivo del Rechazo (Obligatorio)</label>
                                            <textarea id="comment" name="comment" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm" placeholder="Describa claramente las correcciones requeridas..."></textarea>
                                        </div>
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 bg-red-600 border rounded-md font-semibold text-white hover:bg-red-700">
                                            Rechazar Planificación
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
