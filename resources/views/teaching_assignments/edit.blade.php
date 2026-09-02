<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Asignación Docente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('teaching-assignments.update', $teachingAssignment) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="teacher_id" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Docente') }}:</label>
                            <select name="teacher_id" id="teacher_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                <option value="">{{ __('Seleccione un Docente') }}</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id', $teachingAssignment->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }} ({{ $teacher->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="subject_id" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Área — Asignatura') }}:</label>
                            <select name="subject_id" id="subject_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                <option value="">{{ __('Seleccione una Asignatura') }}</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $teachingAssignment->subject_id) == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->academicArea ? $subject->academicArea->name : __('Sin Área') }} — {{ $subject->name }} ({{ $subject->code ?: __('Sin Código') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="course_id" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Curso') }}:</label>
                            <select name="course_id" id="course_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                <option value="">{{ __('Seleccione un Curso') }}</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id', $teachingAssignment->course_id) == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="parallel_id" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Paralelo') }}:</label>
                            <select name="parallel_id" id="parallel_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                <option value="">{{ __('Seleccione un Paralelo') }}</option>
                                @foreach ($parallels as $parallel)
                                    <option value="{{ $parallel->id }}" {{ old('parallel_id', $teachingAssignment->parallel_id) == $parallel->id ? 'selected' : '' }}>
                                        {{ $parallel->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="is_active" class="block text-gray-700 text-sm font-bold mb-2">{{ __('Estado') }}:</label>
                            <select name="is_active" id="is_active" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="1" {{ old('is_active', $teachingAssignment->is_active) ? 'selected' : '' }}>{{ __('Activa') }}</option>
                                <option value="0" {{ !old('is_active', $teachingAssignment->is_active) ? 'selected' : '' }}>{{ __('Inactiva') }}</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('teaching-assignments.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                                {{ __('Cancelar') }}
                            </a>
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Guardar Cambios') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
