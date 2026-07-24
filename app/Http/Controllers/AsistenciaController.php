<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->query('fecha', now()->format('Y-m-d'));

        $registros = Asistencia::with('practicante')
            ->whereDate('fecha', $fecha)
            ->orderBy('hora_entrada')
            ->get();

        $todasFechas = Asistencia::selectRaw('DISTINCT fecha')
            ->orderByDesc('fecha')
            ->pluck('fecha');

        $practicantes = \App\Models\Practicante::orderBy('nombre')->get();

        return view('asistencia.index', compact('registros', 'todasFechas', 'fecha', 'practicantes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'practicante_id' => 'required|exists:practicantes,id',
        ]);

        $practicante = \App\Models\Practicante::find($validated['practicante_id']);
        $fechaActual = now()->format('Y-m-d');

        // Check if already registered today
        $existing = Asistencia::where('practicante_id', $practicante->id)
            ->whereDate('fecha', $fechaActual)
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => 'El practicante ya tiene una asistencia registrada para el día de hoy.']);
        }

        $registro = Asistencia::create([
            'practicante_id' => $practicante->id,
            'fecha' => $fechaActual,
            'hora_entrada' => now()->format('H:i:s'),
        ]);

        return redirect()->route('asistencia.index')
            ->with('status', "Entrada registrada: {$practicante->nombre} a las {$registro->hora_entrada->format('H:i')}");
    }

    public function marcarSalida(Asistencia $asistencia)
    {
        if ($asistencia->hora_salida) {
            return back()->withErrors(['error' => 'Este registro ya tiene salida marcada.']);
        }

        $asistencia->update([
            'hora_salida' => now()->format('H:i:s'),
        ]);

        return redirect()->route('asistencia.index')
            ->with('status', "Salida registrada: {$asistencia->practicante->nombre} a las {$asistencia->hora_salida->format('H:i')}");
    }

    public function destroy(Asistencia $asistencia)
    {
        $nombre = $asistencia->practicante->nombre;
        $asistencia->delete();

        return redirect()->route('asistencia.index')
            ->with('status', "Registro de {$nombre} eliminado.");
    }
    public function regularizar(Request $request)
    {
        $validated = $request->validate([
            'practicante_id' => 'required|exists:practicantes,id',
            'fecha' => 'required|date',
            'hora_entrada' => 'required|date_format:H:i',
            'hora_salida' => 'nullable|date_format:H:i|after:hora_entrada',
        ]);

        $practicante = \App\Models\Practicante::find($validated['practicante_id']);
        
        $asistencia = Asistencia::updateOrCreate(
            [
                'practicante_id' => $practicante->id,
                'fecha' => $validated['fecha'],
            ],
            [
                'hora_entrada' => $validated['hora_entrada'] . ':00',
                'hora_salida' => $validated['hora_salida'] ? $validated['hora_salida'] . ':00' : null,
            ]
        );

        return redirect()->route('asistencia.index', ['fecha' => $validated['fecha']])
            ->with('status', "Asistencia regularizada para {$practicante->nombre} el {$validated['fecha']}");
    }
}
