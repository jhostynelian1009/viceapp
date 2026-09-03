<?php

namespace App\Http\Controllers;

use App\Models\Parallel;
use Illuminate\Http\Request;

class ParallelController extends Controller
{
    public function index()
    {
        $parallels = Parallel::all();

        return view('parallels.index', compact('parallels'));
    }

    public function create()
    {
        return view('parallels.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $parallel = Parallel::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        \App\Services\AuditLogger::log($request->user(), 'parallel.created', $parallel, null, ['name' => $parallel->name, 'is_active' => true]);

        return redirect()->route('parallels.index')
            ->with('success', 'Paralelo creado correctamente.');
    }

    public function edit(Parallel $parallel)
    {
        return view('parallels.edit', compact('parallel'));
    }

    public function update(Request $request, Parallel $parallel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $oldValues = ['name' => $parallel->name, 'is_active' => $parallel->is_active];
        $parallel->update($validated);

        \App\Services\AuditLogger::log($request->user(), 'parallel.updated', $parallel, $oldValues, ['name' => $parallel->name, 'is_active' => $parallel->is_active]);

        return redirect()->route('parallels.index')
            ->with('success', 'Paralelo actualizado correctamente.');
    }

    public function destroy(Parallel $parallel)
    {
        $oldActive = $parallel->is_active;
        $parallel->update(['is_active' => false]);

        \App\Services\AuditLogger::log(auth()->user(), 'parallel.deactivated', $parallel, ['is_active' => $oldActive], ['is_active' => false]);

        return redirect()->route('parallels.index')
            ->with('success', 'Paralelo desactivado correctamente.');
    }

    public function toggleActive(Parallel $parallel)
    {
        $oldActive = $parallel->is_active;
        $parallel->update(['is_active' => ! $parallel->is_active]);

        \App\Services\AuditLogger::log(auth()->user(), 'parallel.status_changed', $parallel, ['is_active' => $oldActive], ['is_active' => $parallel->is_active]);

        return redirect()->route('parallels.index')
            ->with('success', 'Estado del paralelo actualizado correctamente.');
    }
}
