<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Parallel;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class TeachingAssignmentController extends Controller
{
    public function index()
    {
        $assignments = TeachingAssignment::with(['teacher', 'subject.academicArea', 'course', 'parallel'])->get();

        return view('teaching_assignments.index', compact('assignments'));
    }

    public function create()
    {
        // Only active teachers (users with docente role)
        $teachers = User::role('docente')->where('is_active', true)->get();
        // Only active subjects
        $subjects = Subject::active()->with('academicArea')->get();
        // Only active courses
        $courses = Course::active()->get();
        // Only active parallels
        $parallels = Parallel::active()->get();

        return view('teaching_assignments.create', compact('teachers', 'subjects', 'courses', 'parallels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => [
                'required',
                'exists:users,id',
            ],
            'subject_id' => [
                'required',
                'exists:subjects,id',
            ],
            'course_id' => [
                'required',
                'exists:courses,id',
            ],
            'parallel_id' => [
                'required',
                'exists:parallels,id',
            ],
        ]);

        // Validate teacher is active and has docente role
        $teacher = User::role('docente')->where('is_active', true)->find($validated['teacher_id']);
        if (! $teacher) {
            return back()->withErrors(['teacher_id' => 'El docente seleccionado no está activo o no posee el rol docente.'])->withInput();
        }

        // Validate subject is active and has an active academic area
        $subject = Subject::active()->with('academicArea')->find($validated['subject_id']);
        if (! $subject || ! $subject->academicArea || ! $subject->academicArea->is_active) {
            return back()->withErrors(['subject_id' => 'La asignatura seleccionada o su área académica no están activas.'])->withInput();
        }

        // Validate course is active
        $course = Course::active()->find($validated['course_id']);
        if (! $course) {
            return back()->withErrors(['course_id' => 'El curso seleccionado no está activo.'])->withInput();
        }

        // Validate parallel is active
        $parallel = Parallel::active()->find($validated['parallel_id']);
        if (! $parallel) {
            return back()->withErrors(['parallel_id' => 'El paralelo seleccionado no está activo.'])->withInput();
        }

        // Check uniqueness using a database query to prevent race conditions or bypasses
        $exists = TeachingAssignment::where('teacher_id', $validated['teacher_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('course_id', $validated['course_id'])
            ->where('parallel_id', $validated['parallel_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['teacher_id' => 'Esta asignación docente ya existe.'])->withInput();
        }

        TeachingAssignment::create([
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'course_id' => $validated['course_id'],
            'parallel_id' => $validated['parallel_id'],
            'is_active' => true,
        ]);

        return redirect()->route('teaching-assignments.index')
            ->with('success', 'Asignación docente creada correctamente.');
    }

    public function edit(TeachingAssignment $teachingAssignment)
    {
        // Load catalogs including the current assigned IDs even if inactive (for history preservation)
        $teachers = User::role('docente')
            ->where(function ($q) use ($teachingAssignment) {
                $q->where('is_active', true)->orWhere('id', $teachingAssignment->teacher_id);
            })->get();

        $subjects = Subject::where(function ($q) use ($teachingAssignment) {
            $q->where('is_active', true)->orWhere('id', $teachingAssignment->subject_id);
        })->with('academicArea')->get();

        $courses = Course::where(function ($q) use ($teachingAssignment) {
            $q->where('is_active', true)->orWhere('id', $teachingAssignment->course_id);
        })->get();

        $parallels = Parallel::where(function ($q) use ($teachingAssignment) {
            $q->where('is_active', true)->orWhere('id', $teachingAssignment->parallel_id);
        })->get();

        return view('teaching_assignments.edit', compact('teachingAssignment', 'teachers', 'subjects', 'courses', 'parallels'));
    }

    public function update(Request $request, TeachingAssignment $teachingAssignment)
    {
        $validated = $request->validate([
            'teacher_id' => [
                'required',
                'exists:users,id',
            ],
            'subject_id' => [
                'required',
                'exists:subjects,id',
            ],
            'course_id' => [
                'required',
                'exists:courses,id',
            ],
            'parallel_id' => [
                'required',
                'exists:parallels,id',
            ],
            'is_active' => 'sometimes|boolean',
        ]);

        // Validate teacher is active if changed
        if ($validated['teacher_id'] != $teachingAssignment->teacher_id) {
            $teacher = User::role('docente')->where('is_active', true)->find($validated['teacher_id']);
            if (! $teacher) {
                return back()->withErrors(['teacher_id' => 'El docente seleccionado no está activo o no posee el rol docente.'])->withInput();
            }
        }

        // Validate subject is active if changed
        if ($validated['subject_id'] != $teachingAssignment->subject_id) {
            $subject = Subject::active()->with('academicArea')->find($validated['subject_id']);
            if (! $subject || ! $subject->academicArea || ! $subject->academicArea->is_active) {
                return back()->withErrors(['subject_id' => 'La asignatura seleccionada o su área académica no están activas.'])->withInput();
            }
        }

        // Validate course is active if changed
        if ($validated['course_id'] != $teachingAssignment->course_id) {
            $course = Course::active()->find($validated['course_id']);
            if (! $course) {
                return back()->withErrors(['course_id' => 'El curso seleccionado no está activo.'])->withInput();
            }
        }

        // Validate parallel is active if changed
        if ($validated['parallel_id'] != $teachingAssignment->parallel_id) {
            $parallel = Parallel::active()->find($validated['parallel_id']);
            if (! $parallel) {
                return back()->withErrors(['parallel_id' => 'El paralelo seleccionado no está activo.'])->withInput();
            }
        }

        // Validate uniqueness if combination changed
        if (
            $validated['teacher_id'] != $teachingAssignment->teacher_id ||
            $validated['subject_id'] != $teachingAssignment->subject_id ||
            $validated['course_id'] != $teachingAssignment->course_id ||
            $validated['parallel_id'] != $teachingAssignment->parallel_id
        ) {
            $exists = TeachingAssignment::where('teacher_id', $validated['teacher_id'])
                ->where('subject_id', $validated['subject_id'])
                ->where('course_id', $validated['course_id'])
                ->where('parallel_id', $validated['parallel_id'])
                ->exists();

            if ($exists) {
                return back()->withErrors(['teacher_id' => 'Esta asignación docente ya existe.'])->withInput();
            }
        }

        $teachingAssignment->update([
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'course_id' => $validated['course_id'],
            'parallel_id' => $validated['parallel_id'],
            'is_active' => $request->has('is_active') ? $validated['is_active'] : $teachingAssignment->is_active,
        ]);

        return redirect()->route('teaching-assignments.index')
            ->with('success', 'Asignación docente actualizada correctamente.');
    }

    public function destroy(TeachingAssignment $teachingAssignment)
    {
        $teachingAssignment->update(['is_active' => false]);

        return redirect()->route('teaching-assignments.index')
            ->with('success', 'Asignación docente desactivada correctamente.');
    }

    public function toggleActive(TeachingAssignment $teachingAssignment)
    {
        $teachingAssignment->update(['is_active' => ! $teachingAssignment->is_active]);

        return redirect()->route('teaching-assignments.index')
            ->with('success', 'Estado de asignación docente actualizado correctamente.');
    }
}
