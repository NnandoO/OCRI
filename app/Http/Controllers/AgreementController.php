<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Institution;
use App\Models\AgreementType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgreementController extends Controller
{
    /**
     * Módulo: Directorio de Convenios
     */
    public function index()
    {
        // 1. Cargamos convenios con sus relaciones (Eager Loading)
        $agreements = Agreement::with(['institution', 'type'])->latest()->get();

        // 2. Cargamos instituciones y tipos (Evita el error 'Undefined variable')
        $institutions = Institution::all();
        $types = AgreementType::all();

        // 3. Enviamos todo a la vista
        return view('agreements.index', compact('agreements', 'institutions', 'types'));
    }

    /**
     * Módulo: Registro y Documentación (Vista del Formulario)
     */
    public function create()
    {
        $institutions = Institution::all();
        $types = AgreementType::all();

        return view('agreements.create', compact('institutions', 'types'));
    }

    /**
     * Acción: Guardar Convenio y Documento
     */
    public function store(Request $request)
    {
        // 1. Validar los datos
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'resolution_number' => 'required|string|unique:agreements,resolution_number',
            'institution_id' => 'required|exists:institutions,id',
            'agreement_type_id' => 'required|exists:agreement_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'document' => 'nullable|mimes:pdf|max:10240',
        ]);

        // 2. Crear el registro principal del convenio
        $agreement = Agreement::create($validated);

        // 3. Gestión del archivo PDF (si existe)
        if ($request->hasFile('document')) {
            // Guardar en storage/app/public/resoluciones
            $path = $request->file('document')->store('resoluciones', 'public');
            
            // Crear registro en la tabla 'documents' vinculada al convenio
            $agreement->documents()->create([
                'name' => 'Resolución Principal - ' . $agreement->resolution_number,
                'file_path' => $path,
                'extension' => 'pdf',
            ]);
        }

        // Redireccionar al directorio con mensaje de éxito
        return redirect()->route('agreements.index')->with('status', 'Convenio registrado con éxito.');
    }

    /**
     * Módulo: Mapa de Procesos
     */
    public function process()
    {
        return view('agreements.process');
    }
}