<?php

namespace App\Http\Controllers;

use App\Models\AcademicArea;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        // Prevent N+1 query by using eager loading
        $subjects = Subject::with('academicArea')->get();

        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        // Only active academic areas can be associated with new/edited subjects
        $academicAreas = AcademicArea::active()->get();

        return view('subjects.create', compact('academicAreas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'academic_area_id' => 'required|exists:academic_areas,id',
            'code' => 'nullable|string|max:255|unique:subjects,code',
        ]);

        // Validate that the area is active
        $area = AcademicArea::findOrFail($validated['academic_area_id']);
        if (! $area->is_active) {
            return back()->withErrors(['academic_area_id' => 'El área académica seleccionada no está activa.'])->withInput();
        }

        Subject::create([
            'name' => $validated['name'],
            'academic_area_id' => $validated['academic_area_id'],
            'code' => $validated['code'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('subjects.index')
            ->with('success', 'Asignatura creada correctamente.');
    }

    public function edit(Subject $subject)
    {
        // Load active areas, plus the subject's current area even if inactive (for history)
        $academicAreas = AcademicArea::where('is_active', true)
            ->orWhere('id', $subject->academic_area_id)
            ->get();

        return view('subjects.edit', compact('subject', 'academicAreas'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'academic_area_id' => 'required|exists:academic_areas,id',
            'code' => 'nullable|string|max:255|unique:subjects,code,'.$subject->id,
            'is_active' => 'sometimes|boolean',
        ]);

        // Validate that the area is active (or unchanged)
        if ($validated['academic_area_id'] != $subject->academic_area_id) {
            $area = AcademicArea::findOrFail($validated['academic_area_id']);
            if (! $area->is_active) {
                return back()->withErrors(['academic_area_id' => 'El área académica seleccionada no está activa.'])->withInput();
            }
        }

        $subject->update([
            'name' => $validated['name'],
            'academic_area_id' => $validated['academic_area_id'],
            'code' => $validated['code'] ?? null,
            'is_active' => $request->has('is_active') ? $validated['is_active'] : $subject->is_active,
        ]);

        return redirect()->route('subjects.index')
            ->with('success', 'Asignatura actualizada correctamente.');
    }

    public function destroy(Subject $subject)
    {
        $subject->update(['is_active' => false]);

        return redirect()->route('subjects.index')
            ->with('success', 'Asignatura desactivada correctamente.');
    }

    public function toggleActive(Subject $subject)
    {
        $subject->update(['is_active' => ! $subject->is_active]);

        return redirect()->route('subjects.index')
            ->with('success', 'Estado de la asignatura actualizado correctamente.');
    }
}
