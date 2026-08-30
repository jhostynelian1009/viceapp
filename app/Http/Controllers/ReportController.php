<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\Subject;
use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('reports.view');

        $reportData = null;
        $teachers = User::role('docente')->get();
        $subjects = Subject::all();

        $query = Planning::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $query->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date')]);
        }

        if ($request->filled('teacher_id')) {
            $query->where('user_id', $request->input('teacher_id'));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->anyfilled(['start_date', 'teacher_id', 'subject_id', 'status'])) {
            $reportData = $query->with(['user', 'subject'])->get();
        }

        return view('reports.index', compact('reportData', 'teachers', 'subjects'));
    }

    public function download(Request $request, $type)
    {
        Gate::authorize('reports.export');

        $query = Planning::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date')]);
        }

        if ($request->filled('teacher_id')) {
            $query->where('user_id', $request->input('teacher_id'));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $reportData = $query->with(['user', 'subject'])->get();

        if ($type == 'pdf') {
            return $this->downloadPdf($reportData);
        } elseif ($type == 'word') {
            return $this->downloadWord($reportData);
        }

        return redirect()->back();
    }

    private function downloadPdf($data)
    {
        $pdf = new Dompdf;
        $pdf->loadHtml(view('reports.pdf', compact('data'))->render());
        $pdf->render();

        return $pdf->stream('report.pdf');
    }

    private function downloadWord($data)
    {
        $phpWord = new PhpWord;
        $section = $phpWord->addSection();
        $section->addText('Reporte de Planificaciones', ['bold' => true, 'size' => 16]);

        $table = $section->addTable([
            'borderColor' => '000000',
            'borderSize' => 6,
            'cellMargin' => 50,
        ]);

        $table->addRow();
        $table->addCell(2000)->addText('Docente', ['bold' => true]);
        $table->addCell(2000)->addText('Área Académica', ['bold' => true]);
        $table->addCell(2000)->addText('Estado', ['bold' => true]);
        $table->addCell(2000)->addText('Fecha', ['bold' => true]);

        foreach ($data as $item) {
            $table->addRow();
            $table->addCell()->addText($item->user->name ?? 'N/A');
            $table->addCell()->addText($item->subject->name ?? 'N/A');
            $table->addCell()->addText(ucfirst($item->status));
            $table->addCell()->addText($item->created_at->format('d/m/Y H:i'));
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'report.docx';
        $objWriter->save($fileName);

        return response()->download($fileName)->deleteFileAfterSend(true);
    }
}
