<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();

        return view('courses.index', compact('courses'));
    }

    public function create()
    {
        return view('courses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $course = Course::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        \App\Services\AuditLogger::log($request->user(), 'course.created', $course, null, ['name' => $course->name, 'is_active' => true]);

        return redirect()->route('courses.index')
            ->with('success', 'Curso creado correctamente.');
    }

    public function edit(Course $course)
    {
        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $oldValues = ['name' => $course->name, 'is_active' => $course->is_active];
        $course->update($validated);

        \App\Services\AuditLogger::log($request->user(), 'course.updated', $course, $oldValues, ['name' => $course->name, 'is_active' => $course->is_active]);

        return redirect()->route('courses.index')
            ->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Course $course)
    {
        $oldActive = $course->is_active;
        $course->update(['is_active' => false]);

        \App\Services\AuditLogger::log(auth()->user(), 'course.deactivated', $course, ['is_active' => $oldActive], ['is_active' => false]);

        return redirect()->route('courses.index')
            ->with('success', 'Curso desactivado correctamente.');
    }

    public function toggleActive(Course $course)
    {
        $oldActive = $course->is_active;
        $course->update(['is_active' => ! $course->is_active]);

        \App\Services\AuditLogger::log(auth()->user(), 'course.status_changed', $course, ['is_active' => $oldActive], ['is_active' => $course->is_active]);

        return redirect()->route('courses.index')
            ->with('success', 'Estado del curso actualizado correctamente.');
    }
}
