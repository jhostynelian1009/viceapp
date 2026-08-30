<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Control') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Mensaje de Bienvenida -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900">¡Bienvenido, {{ Auth::user()->name }}!</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Nos alegra tenerte de vuelta. Desde aquí puedes gestionar las planificaciones y otros recursos del sistema.
                    </p>
                </div>
            </div>

            <!-- Menú de Tarjetas de Funcionalidades -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Tarjeta de Planificaciones (Visible para todos) -->
                <a href="{{ route('plannings.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg transform hover:scale-105 transition-transform duration-300">
                    <div class="p-6 text-center">
                        <div class="flex justify-center mb-4">
                            <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Gestionar Planificaciones</h3>
                        <p class="mt-2 text-sm text-gray-600">Crea, edita y visualiza las planificaciones de los docentes.</p>
                    </div>
                </a>

                @if(Auth::user()->hasRole('secretaria') || Auth::user()->hasRole('vicerrectorado'))
                    <!-- Tarjeta de Docentes (Solo para Secretaria y Vicerrector) -->
                    <a href="{{ route('teachers.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg transform hover:scale-105 transition-transform duration-300">
                        <div class="p-6 text-center">
                            <div class="flex justify-center mb-4">
                                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.124-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.124-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Administrar Docentes</h3>
                            <p class="mt-2 text-sm text-gray-600">Gestiona la información y los perfiles de los docentes.</p>
                        </div>
                    </a>

                    <!-- Tarjeta de Reportes (Solo para Secretaria y Vicerrector) -->
                    <a href="{{ route('reports.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg transform hover:scale-105 transition-transform duration-300">
                        <div class="p-6 text-center">
                            <div class="flex justify-center mb-4">
                                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Generar Reportes</h3>
                            <p class="mt-2 text-sm text-gray-600">Crea informes y estadísticas sobre el progreso de las planificaciones.</p>
                        </div>
                    </a>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
