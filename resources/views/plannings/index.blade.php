<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Planificaciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Tarjeta de Bienvenida Principal -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border-l-4 border-red-600">
                <div class="p-8 flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-12 w-12 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                    </div>
                    <div class="ml-6">
                        <h3 class="text-2xl font-bold text-gray-900">¡Bienvenido, {{ Auth::user()->name }}!</h3>
                        <p class="mt-1 text-gray-600">Este es el centro de control para la gestión de planificaciones académicas.</p>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de Acceso Rápido (Dinámicas por Rol) -->
            @auth
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    @can('create', App\Models\Planning::class)
                        <!-- Card para Subir Planificación -->
                        <a href="#upload-section" class="block transform hover:scale-105 transition-transform duration-300">
                            <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl h-full flex flex-col justify-between">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-red-100 p-3 rounded-full">
                                            <svg class="h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        </div>
                                        <div class="ml-5">
                                            <h4 class="text-xl font-semibold text-gray-800">Subir Planificación</h4>
                                            <p class="mt-1 text-gray-500">Añade un nuevo documento para su revisión.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endcan

                    <!-- Card para Ver Mis Planificaciones / Listado -->
                    <a href="#plannings-list" class="block transform hover:scale-105 transition-transform duration-300">
                        <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl h-full flex flex-col justify-between">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-red-100 p-3 rounded-full">
                                        <svg class="h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11h10M7 15h5" /></svg>
                                    </div>
                                    <div class="ml-5">
                                        <h4 class="text-xl font-semibold text-gray-800">Gestionar Documentos</h4>
                                        <p class="mt-1 text-gray-500">Revisa, filtra y gestiona planificaciones.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                    @can('review', App\Models\Planning::class)
                        <!-- Card para Revisar Planificaciones (Solo Vicerrectorado) -->
                        <a href="{{ route('plannings.review') }}" class="block transform hover:scale-105 transition-transform duration-300">
                            <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl h-full flex flex-col justify-between border-2 border-blue-500">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-blue-100 p-3 rounded-full">
                                            <svg class="h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                        </div>
                                        <div class="ml-5">
                                            <h4 class="text-xl font-semibold text-gray-800">Revisar Planificaciones de Docentes</h4>
                                            <p class="mt-1 text-gray-500">Accede para aprobar o rechazar documentos.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endcan
                </div>
            @endauth

            @can('create', App\Models\Planning::class)
                <!-- Sección para Subir Nueva Planificación (Docentes) -->
                <div id="upload-section" class="pt-8">
                     <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">Subir Nueva Planificación</h3>
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

                            <form action="{{ route('plannings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                                @csrf
                                <div>
                                    <x-input-label for="title" :value="__('Título de la Planificación')" class="text-base"/>
                                    <x-text-input id="title" class="block mt-2 w-full" type="text" name="title" :value="old('title')" required autofocus />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="assignment_id" :value="__('Asignación Académica (Asignatura — Curso — Paralelo)')" class="text-base"/>
                                    <select id="assignment_id" name="assignment_id" class="block mt-2 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm" required>
                                        <option value="">Seleccione una asignación</option>
                                        @foreach($assignments as $assignment)
                                            <option value="{{ $assignment->id }}" {{ old('assignment_id') == $assignment->id ? 'selected' : '' }}>
                                                {{ $assignment->subject ? ($assignment->subject->academicArea ? $assignment->subject->academicArea->name : __('Sin Área')) . ' — ' . $assignment->subject->name : __('Sin Asignatura') }} (Curso: {{ $assignment->course ? $assignment->course->name : __('Sin Curso') }} — Paralelo: {{ $assignment->parallel ? $assignment->parallel->name : __('Sin Paralelo') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('assignment_id')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="week_start" :value="__('Fecha Inicio de Semana')" class="text-base"/>
                                        <x-text-input id="week_start" class="block mt-2 w-full" type="date" name="week_start" :value="old('week_start')" required />
                                        <x-input-error :messages="$errors->get('week_start')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="week_end" :value="__('Fecha Fin de Semana')" class="text-base"/>
                                        <x-text-input id="week_end" class="block mt-2 w-full" type="date" name="week_end" :value="old('week_end')" required />
                                        <x-input-error :messages="$errors->get('week_end')" class="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="file" :value="__('Archivo (PDF, DOC, DOCX)')" class="text-base"/>
                                    <input id="file" class="block mt-2 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" type="file" name="file" required />
                                    <p class="mt-1 text-sm text-gray-500">Tamaño máximo: 10MB.</p>
                                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                                </div>

                                <div class="flex items-center justify-end pt-4">
                                    <button type="submit" class="px-6 py-2 bg-red-600 text-white font-bold rounded-md hover:bg-red-700 transition-colors">
                                        {{ __('Subir Planificación') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan

            <!-- Sección de Listado de Planificaciones -->
            <div id="plannings-list" class="mt-12 pt-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Listado de Planificaciones</h3>

                        <form action="{{ route('plannings.index') }}" method="GET" class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div class="md:col-span-1">
                                    <x-input-label for="search" :value="__('Buscar por Título')" />
                                    <x-text-input id="search" class="block mt-1 w-full" type="text" name="search" :value="request('search')" placeholder="Escribe un título..."/>
                                </div>
                                <div>
                                    <x-input-label for="status" :value="__('Filtrar por Estado')" />
                                    <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm">
                                        <option value="">Todos los Estados</option>
                                        <option value="borrador" @if(request('status') == 'borrador') selected @endif>Borrador</option>
                                        <option value="revisión" @if(request('status') == 'revisión') selected @endif>En Revisión</option>
                                        <option value="aprobado" @if(request('status') == 'aprobado') selected @endif>Aprobado</option>
                                        <option value="rechazado" @if(request('status') == 'rechazado') selected @endif>Rechazado</option>
                                    </select>
                                </div>
                                <div class="flex items-center">
                                    <x-primary-button class="w-full md:w-auto justify-center">
                                        {{ __('Filtrar') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>

                        <div class="overflow-x-auto border-t">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Docente</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clasificación Académica</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semana</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Subida</th>
                                        <th scope="col" class="relative px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($plannings as $planning)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">{{ $planning->title }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $planning->user->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                                @if($planning->assignment)
                                                    {{ $planning->assignment->subject ? $planning->assignment->subject->name : 'N/A' }} —
                                                    {{ $planning->assignment->course ? $planning->assignment->course->name : 'N/A' }} —
                                                    {{ $planning->assignment->parallel ? $planning->assignment->parallel->name : 'N/A' }}
                                                @else
                                                    {{ $planning->subject->name ?? 'N/A' }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-semibold">
                                                @if($planning->week_start && $planning->week_end)
                                                    {{ $planning->week_start->format('d/m/Y') }} al {{ $planning->week_end->format('d/m/Y') }}
                                                @else
                                                    <span class="text-gray-400 italic">No asignada</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $planning->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                @can('view', $planning)
                                                    <a href="{{ route('plannings.view', $planning) }}" class="text-red-600 hover:text-red-800 font-semibold">Ver Detalles</a>
                                                @endcan

                                                @can('submit', $planning)
                                                    <form action="{{ route('plannings.submit', $planning) }}" method="POST" class="inline-block ml-4">
                                                        @csrf
                                                        <button type="submit" class="text-blue-600 hover:text-blue-800 font-semibold">Enviar a Revisión</button>
                                                    </form>
                                                @endcan

                                                @can('delete', $planning)
                                                    <form action="{{ route('plannings.destroy', $planning) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta planificación? Esta acción no se puede deshacer.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-gray-600 hover:text-gray-800 font-semibold">Eliminar</button>
                                                    </form>
                                                @endcan

                                                @cannot('view', $planning)
                                                    <span class="text-gray-400 italic text-xs">Sin acciones académicas</span>
                                                @endcannot
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 whitespace-nowrap text-sm text-gray-500 text-center">
                                                <p>No se encontraron planificaciones con los criterios de búsqueda.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">
                            {{ $plannings->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
