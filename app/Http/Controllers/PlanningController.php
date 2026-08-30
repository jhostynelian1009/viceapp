<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\Subject;
use App\Rules\AcademicDocumentRule;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $query = Planning::with('user', 'subject');

        if ($user->hasRole('docente')) {
            $query->where('user_id', $user->id);
        }

        if ($request->has('search') && $request->input('search') != '') {
            $query->where('title', 'like', '%'.$request->input('search').'%');
        }

        if ($request->has('status') && $request->input('status') != '') {
            $query->where('status', $request->input('status'));
        }

        $plannings = $query->latest()->paginate(10);
        $subjects = Subject::all();

        return view('plannings.index', compact('plannings', 'subjects'));
    }

    public function review(Request $request)
    {
        Gate::authorize('review', Planning::class);

        $plannings = Planning::with('user', 'subject')
            ->where('status', 'revisión')
            ->latest()
            ->paginate(15);

        return view('plannings.review', compact('plannings'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Planning::class);

        // Flujo para archivos de Google Drive (deshabilitado / postergado)
        if ($request->has('google_drive_file_id')) {
            return redirect()->back()->with('error', 'La carga desde Google Drive no está activa en este momento.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                new AcademicDocumentRule,
            ],
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $uploadedFile = $request->file('file');
        $originalExtension = strtolower($uploadedFile->getClientOriginalExtension());
        $randomFileName = Str::random(40).'.'.$originalExtension;

        $path = null;
        try {
            DB::beginTransaction();

            // Guardar exclusivamente en el disco privado
            $path = $uploadedFile->storeAs('', $randomFileName, 'private_plannings');

            if (! $path) {
                throw new \Exception('No se pudo guardar el archivo en el almacenamiento privado.');
            }

            Planning::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'file_path' => $path,
                'subject_id' => $request->subject_id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            // Limpieza compensatoria: eliminar el archivo si ya fue escrito
            if ($path) {
                Storage::disk('private_plannings')->delete($path);
            }

            // Registrar el error de forma segura en el servidor
            Log::error('Error al subir planificación: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'subject_id' => $request->subject_id,
            ]);

            return redirect()->back()->with('error', 'No se pudo procesar la planificación. Inténtelo de nuevo o contacte con el administrador.');
        }

        return redirect()->route('plannings.index')->with('success', 'Planificación subida exitosamente en almacenamiento privado.');
    }

    public function download(Planning $planning)
    {
        Gate::authorize('download', $planning);

        $disk = Storage::disk('private_plannings');

        if (! $planning->file_path || ! $disk->exists($planning->file_path)) {
            abort(404, 'El archivo de la planificación no existe o no fue encontrado.');
        }

        $extension = strtolower(pathinfo($planning->file_path, PATHINFO_EXTENSION));
        $downloadName = Str::slug($planning->title).'.'.$extension;

        return $disk->download($planning->file_path, $downloadName);
    }

    public function preview(Planning $planning)
    {
        Gate::authorize('view', $planning);

        $disk = Storage::disk('private_plannings');

        if (! $planning->file_path || ! $disk->exists($planning->file_path)) {
            abort(404, 'El archivo de la planificación no existe o no fue encontrado.');
        }

        $extension = strtolower(pathinfo($planning->file_path, PATHINFO_EXTENSION));

        if ($extension === 'pdf') {
            return response()->file($disk->path($planning->file_path), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.Str::slug($planning->title).'.pdf"',
            ]);
        }

        // Para DOC y DOCX degradar a descarga
        $downloadName = Str::slug($planning->title).'.'.$extension;

        return $disk->download($planning->file_path, $downloadName);
    }

    public function view(Planning $planning)
    {
        Gate::authorize('view', $planning);

        $planning->load('comments.user', 'subject');

        return view('plannings.view', compact('planning'));
    }

    public function updateStatus(Request $request, Planning $planning)
    {
        $request->validate([
            'status' => 'required|in:borrador,revisión,aprobado,rechazado',
        ]);

        $newStatus = $request->status;

        if ($newStatus === 'revisión') {
            Gate::authorize('submit', $planning);
            $redirectRoute = 'plannings.index';
        } elseif ($newStatus === 'aprobado') {
            Gate::authorize('approve', $planning);
            $redirectRoute = 'plannings.review';
        } elseif ($newStatus === 'rechazado') {
            Gate::authorize('reject', $planning);
            $redirectRoute = 'plannings.review';
        } else {
            // Re-draft or invalid status transition
            Gate::authorize('update', $planning);
            $redirectRoute = 'plannings.index';
        }

        $planning->update(['status' => $newStatus]);

        return redirect()->route($redirectRoute)->with('success', 'El estado de la planificación ha sido actualizado correctamente.');
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
