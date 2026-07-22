<?php

namespace App\Http\Controllers;

use App\Models\Practicante;
use Illuminate\Http\Request;

class PracticanteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'dni' => 'required|string|size:8|unique:practicantes,dni',
        ]);

        $nombre = strtoupper(trim($validated['nombre']));

        Practicante::create([
            'nombre' => $nombre,
            'dni' => $validated['dni'],
        ]);

        return redirect()->route('asistencia.index')->with('status', 'Practicante registrado exitosamente.');
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
}
