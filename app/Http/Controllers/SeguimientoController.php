<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\AgreementReport;
use App\Models\WorkPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeguimientoController extends Controller
{
    public function index()
    {
        // Solo convenios vigentes
        $agreements = Agreement::where('status', 'Vigente')
            ->orderBy('end_date', 'asc')
            ->paginate(10);

        return view('seguimiento.index', compact('agreements'));
    }

    public function show(Agreement $agreement)
    {
        if ($agreement->status !== 'Vigente') {
            return redirect()->route('seguimiento.index')->withErrors('El convenio no está vigente.');
        }

        $agreement->load(['workPlan', 'reports']);

        return view('seguimiento.show', compact('agreement'));
    }

    public function storePlan(Request $request, Agreement $agreement)
    {
        $request->validate([
            'work_plan_file' => 'required|file|max:10240', // max 10MB
        ]);

        $file = $request->file('work_plan_file');
        $path = $file->store('work_plans', 'public');

        if ($agreement->workPlan) {
            Storage::disk('public')->delete($agreement->workPlan->file_path);
            $agreement->workPlan->update([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        } else {
            $agreement->workPlan()->create([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        return back()->with('status', 'Plan de trabajo subido correctamente.');
    }

    public function storeReport(Request $request, Agreement $agreement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'oficio_file' => 'nullable|file|max:10240',
            'respuesta_file' => 'nullable|file|max:10240',
        ]);

        $report = new AgreementReport([
            'title' => $request->title,
            'date' => $request->date,
        ]);

        if ($request->hasFile('oficio_file')) {
            $report->oficio_path = $request->file('oficio_file')->store('reports', 'public');
            $report->oficio_original_name = $request->file('oficio_file')->getClientOriginalName();
        }

        if ($request->hasFile('respuesta_file')) {
            $report->respuesta_path = $request->file('respuesta_file')->store('reports', 'public');
            $report->respuesta_original_name = $request->file('respuesta_file')->getClientOriginalName();
        }

        $agreement->reports()->save($report);

        return back()->with('status', 'Informe agregado correctamente.');
    }
}
