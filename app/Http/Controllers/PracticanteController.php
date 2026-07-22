<?php

namespace App\Http\Controllers;

use App\Models\Practicante;
use Illuminate\Http\Request;

class PracticanteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:practicantes,nombre',
            'dni' => 'required|string|size:8|unique:practicantes,dni',
        ], [
            'nombre.unique' => 'Este practicante ya se encuentra registrado en el sistema.',
            'dni.unique' => 'Este DNI ya está registrado en el sistema.'
        ]);

        $nombre = strtoupper(trim($validated['nombre']));
        $dni = $validated['dni'];

        Practicante::create([
            'nombre' => $nombre,
            'dni' => $dni,
        ]);

        $email = "{$dni}@practicante.uncp.edu.pe";

        // Crear usuario para que el practicante inicie sesión
        if (!\App\Models\User::where('email', $email)->exists()) {
            \App\Models\User::create([
                'name' => mb_convert_case($nombre, MB_CASE_TITLE, "UTF-8"),
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make($dni),
                'role' => 'practicante'
            ]);
        }

        return redirect()->route('asistencia.index')->with('status', 'Practicante registrado exitosamente.')->with('credentials', [
            'email' => $email,
            'password' => $dni
        ]);
    }

    public function show(Practicante $practicante)
    {
        $asistencias = $practicante->asistencias()->orderByDesc('fecha')->get();

        $totalMinutes = 0;

        foreach ($asistencias as $asistencia) {
            if ($asistencia->hora_entrada && $asistencia->hora_salida) {
                // Carbon diffInMinutes handles the time difference
                $totalMinutes += $asistencia->hora_entrada->diffInMinutes($asistencia->hora_salida);
            }
        }

        $horas = floor($totalMinutes / 60);
        $minutos = $totalMinutes % 60;
        
        $totalHoras = "{$horas}h {$minutos}m";

        return view('practicantes.show', compact('practicante', 'asistencias', 'totalHoras'));
    }

    public function export(Practicante $practicante)
    {
        $fileName = 'Reporte_Asistencia_' . str_replace(' ', '_', $practicante->nombre) . '_' . now()->format('Ymd') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PracticanteExport($practicante), $fileName);
    }
}
