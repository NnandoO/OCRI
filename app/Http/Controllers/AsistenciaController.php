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

        $registro = Asistencia::create([
            'practicante_id' => $practicante->id,
            'fecha' => now()->format('Y-m-d'),
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
}
