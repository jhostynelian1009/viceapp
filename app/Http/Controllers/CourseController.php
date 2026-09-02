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

        Course::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

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

        $course->update($validated);

        return redirect()->route('courses.index')
            ->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Course $course)
    {
        $course->update(['is_active' => false]);

        return redirect()->route('courses.index')
            ->with('success', 'Curso desactivado correctamente.');
    }

    public function toggleActive(Course $course)
    {
        $course->update(['is_active' => ! $course->is_active]);

        return redirect()->route('courses.index')
            ->with('success', 'Estado del curso actualizado correctamente.');
    }
}
