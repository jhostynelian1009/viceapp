<?php

namespace App\Http\Controllers;

use App\Enums\PlanningStatus;
use App\Models\Planning;
use App\Models\PlanningVersion;
use App\Rules\AcademicDocumentRule;
use App\Services\PlanningWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Planning::class);

        $user = Auth::user();
        $query = Planning::with(['user', 'subject', 'assignment.course', 'assignment.parallel', 'assignment.subject.academicArea', 'currentVersion']);

        if ($user->hasRole('docente')) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->input('search').'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $plannings = $query->latest()->paginate(10);

        $assignments = [];
        if ($user->hasRole('docente')) {
            $assignments = \App\Models\TeachingAssignment::where('teacher_id', $user->id)
                ->where('is_active', true)
                ->with(['subject.academicArea', 'course', 'parallel'])
                ->get();
        }

        return view('plannings.index', compact('plannings', 'assignments'));
    }

    public function review(Request $request)
    {
        Gate::authorize('review', Planning::class);

        $plannings = Planning::with(['user', 'subject', 'assignment.course', 'assignment.parallel', 'assignment.subject.academicArea', 'currentVersion'])
            ->where('status', PlanningStatus::PENDING)
            ->latest()
            ->paginate(15);

        return view('plannings.review', compact('plannings'));
    }

    public function store(Request $request, PlanningWorkflowService $workflow)
    {
        Gate::authorize('create', Planning::class);

        if ($request->has('google_drive_file_id')) {
            return redirect()->back()->with('error', 'La carga desde Google Drive no está activa en este momento.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => [
                'required',
                'file',
                'max:10240',
                new AcademicDocumentRule,
            ],
            'assignment_id' => 'required|exists:teaching_assignments,id',
            'week_start' => 'required|date',
            'week_end' => 'required|date|after_or_equal:week_start',
        ]);

        try {
            $workflow->createDraft($request->user(), $request->only(['title', 'assignment_id', 'week_start', 'week_end']), $request->file('file'));
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return redirect()->back()->withErrors($ve->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Error al crear borrador de planificación: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'assignment_id' => $request->assignment_id,
            ]);

            return redirect()->back()->with('error', 'No se pudo procesar la planificación. Inténtelo de nuevo o contacte con el administrador.')->withInput();
        }

        return redirect()->route('plannings.index')->with('success', 'Planificación creada como borrador en almacenamiento privado.');
    }

    public function update(Request $request, Planning $planning, PlanningWorkflowService $workflow)
    {
        Gate::authorize('update', $planning);

        $rules = [
            'title' => 'required|string|max:255',
            'week_start' => 'required|date',
            'week_end' => 'required|date|after_or_equal:week_start',
        ];

        if ($request->hasFile('file')) {
            $rules['file'] = ['file', 'max:10240', new AcademicDocumentRule];
        }

        $request->validate($rules);

        try {
            $workflow->updateDraft(
                $planning,
                $request->user(),
                $request->only(['title', 'week_start', 'week_end']),
                $request->file('file')
            );
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return redirect()->back()->withErrors($ve->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Error al actualizar planificación: '.$e->getMessage(), ['planning_id' => $planning->id]);

            return redirect()->back()->with('error', 'No se pudo actualizar la planificación. Inténtelo de nuevo.');
        }

        return redirect()->route('plannings.view', $planning)->with('success', 'Planificación actualizada exitosamente.');
    }

    public function submit(Planning $planning, PlanningWorkflowService $workflow)
    {
        Gate::authorize('submit', $planning);

        try {
            $workflow->submit($planning, Auth::user());
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return redirect()->back()->withErrors($ve->errors());
        } catch (\Throwable $e) {
            Log::error('Error al enviar planificación: '.$e->getMessage(), ['planning_id' => $planning->id]);

            return redirect()->back()->with('error', 'No se pudo enviar la planificación.');
        }

        return redirect()->route('plannings.index')->with('success', 'Planificación enviada a revisión exitosamente.');
    }

    public function approve(Planning $planning, PlanningWorkflowService $workflow)
    {
        Gate::authorize('approve', $planning);

        try {
            $workflow->approve($planning, Auth::user());
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return redirect()->back()->withErrors($ve->errors());
        } catch (\Throwable $e) {
            Log::error('Error al aprobar planificación: '.$e->getMessage(), ['planning_id' => $planning->id]);

            return redirect()->back()->with('error', 'No se pudo aprobar la planificación.');
        }

        return redirect()->route('plannings.review')->with('success', 'Planificación aprobada exitosamente.');
    }

    public function reject(Request $request, Planning $planning, PlanningWorkflowService $workflow)
    {
        Gate::authorize('reject', $planning);

        $request->validate([
            'comment' => 'required|string|min:3|max:1000',
        ], [
            'comment.required' => 'El motivo de rechazo es obligatorio.',
            'comment.min' => 'El motivo de rechazo debe contener al menos 3 caracteres.',
        ]);

        try {
            $workflow->reject($planning, Auth::user(), $request->input('comment'));
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return redirect()->back()->withErrors($ve->errors());
        } catch (\Throwable $e) {
            Log::error('Error al rechazar planificación: '.$e->getMessage(), ['planning_id' => $planning->id]);

            return redirect()->back()->with('error', 'No se pudo rechazar la planificación.');
        }

        return redirect()->route('plannings.review')->with('success', 'Planificación rechazada exitosamente.');
    }

    public function download(Planning $planning)
    {
        Gate::authorize('download', $planning);

        $currentVersion = $planning->currentVersion;
        if ($currentVersion && $currentVersion->isMissingFile()) {
            abort(404, 'El archivo de la planificación no se encuentra disponible.');
        }

        $filePath = $currentVersion ? $currentVersion->file_path : $planning->file_path;
        $diskName = $currentVersion ? $currentVersion->file_disk : 'private_plannings';

        $disk = Storage::disk($diskName);

        if (! $filePath || ! $disk->exists($filePath)) {
            abort(404, 'El archivo de la planificación no existe o no fue encontrado.');
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $downloadName = Str::slug($planning->title).'.'.$extension;

        return $disk->download($filePath, $downloadName);
    }

    public function preview(Planning $planning)
    {
        Gate::authorize('view', $planning);

        $currentVersion = $planning->currentVersion;
        if ($currentVersion && $currentVersion->isMissingFile()) {
            abort(404, 'El archivo de la planificación no se encuentra disponible.');
        }

        $filePath = $currentVersion ? $currentVersion->file_path : $planning->file_path;
        $diskName = $currentVersion ? $currentVersion->file_disk : 'private_plannings';

        $disk = Storage::disk($diskName);

        if (! $filePath || ! $disk->exists($filePath)) {
            abort(404, 'El archivo de la planificación no existe o no fue encontrado.');
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'pdf') {
            return response()->file($disk->path($filePath), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.Str::slug($planning->title).'.pdf"',
            ]);
        }

        $downloadName = Str::slug($planning->title).'.'.$extension;

        return $disk->download($filePath, $downloadName);
    }

    public function view(Planning $planning)
    {
        Gate::authorize('view', $planning);

        $planning->load(['comments.user', 'subject', 'currentVersion', 'versions.uploader', 'reviews.reviewer', 'assignment.course', 'assignment.parallel']);

        return view('plannings.view', compact('planning'));
    }

    public function downloadVersion(Planning $planning, PlanningVersion $version)
    {
        Gate::authorize('download', $planning);

        if ($version->planning_id !== $planning->id) {
            abort(403, 'La versión solicitada no pertenece a esta planificación.');
        }

        if ($version->isMissingFile()) {
            abort(404, 'El archivo de esta versión no se encuentra disponible.');
        }

        $disk = Storage::disk($version->file_disk ?: 'private_plannings');

        if (! $version->file_path || ! $disk->exists($version->file_path)) {
            abort(404, 'El archivo de esta versión no fue encontrado.');
        }

        $extension = strtolower(pathinfo($version->original_name ?: $version->file_path, PATHINFO_EXTENSION));
        $downloadName = Str::slug($planning->title).'-v'.$version->version.'.'.$extension;

        return $disk->download($version->file_path, $downloadName);
    }

    public function destroy(Planning $planning)
    {
        Gate::authorize('delete', $planning);

        if ($planning->file_path) {
            Storage::disk('private_plannings')->delete($planning->file_path);
        }

        $planning->delete();

        return redirect()->route('plannings.index')->with('success', 'Planificación eliminada exitosamente.');
    }
}
