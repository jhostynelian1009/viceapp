<?php

namespace App\Http\Controllers;

use App\Models\AcademicArea;
use Illuminate\Http\Request;

class AcademicAreaController extends Controller
{
    public function index()
    {
        $academicAreas = AcademicArea::all();

        return view('academic_areas.index', compact('academicAreas'));
    }

    public function create()
    {
        return view('academic_areas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:academic_areas,code',
        ]);

        AcademicArea::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'is_active' => true,
        ]);

        return redirect()->route('academic-areas.index')
            ->with('success', 'Área académica creada correctamente.');
    }

    public function edit(AcademicArea $academicArea)
    {
        return view('academic_areas.edit', compact('academicArea'));
    }

    public function update(Request $request, AcademicArea $academicArea)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:academic_areas,code,'.$academicArea->id,
            'is_active' => 'sometimes|boolean',
        ]);

        $academicArea->update($validated);

        return redirect()->route('academic-areas.index')
            ->with('success', 'Área académica actualizada correctamente.');
    }

    public function destroy(AcademicArea $academicArea)
    {
        $academicArea->update(['is_active' => false]);

        return redirect()->route('academic-areas.index')
            ->with('success', 'Área académica desactivada correctamente.');
    }

    public function toggleActive(AcademicArea $academicArea)
    {
        $academicArea->update(['is_active' => ! $academicArea->is_active]);

        return redirect()->route('academic-areas.index')
            ->with('success', 'Estado de área académica actualizado correctamente.');
    }
}
