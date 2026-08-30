<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        $teachers = User::role('docente')->latest()->paginate(10);

        return view('teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', User::class);

        return view('teachers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->assignRole('docente');

        return redirect()->route('teachers.index')
            ->with('success', 'Docente creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $teacher)
    {
        Gate::authorize('view', $teacher);

        return view('teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $teacher)
    {
        Gate::authorize('update', $teacher);

        return view('teachers.edit', compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, User $teacher)
    {
        $validated = $request->validated();

        $teacher->name = $validated['name'];
        $teacher->email = $validated['email'];
        if (! empty($validated['password'])) {
            $teacher->password = Hash::make($validated['password']);
        }
        $teacher->save();

        return redirect()->route('teachers.index')
            ->with('success', 'Docente actualizado exitosamente.');
    }

    /**
     * Toggle active state instead of physical deletion.
     */
    public function toggleActive(Request $request, User $teacher)
    {
        Gate::authorize('toggleActive', $teacher);

        $teacher->is_active = ! $teacher->is_active;
        $teacher->save();

        $statusMessage = $teacher->is_active ? 'cuenta activada' : 'cuenta desactivada';

        return redirect()->route('teachers.index')
            ->with('success', "Estado del docente actualizado ({$statusMessage}).");
    }

    /**
     * Remove (deactivate) the specified resource from storage.
     */
    public function destroy(User $teacher)
    {
        Gate::authorize('toggleActive', $teacher);

        $teacher->is_active = false;
        $teacher->save();

        return redirect()->route('teachers.index')
            ->with('success', 'Docente desactivado exitosamente.');
    }
}
