<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->query('fecha', now()->format('Y-m-d'));

        $registros = Asistencia::whereDate('fecha', $fecha)
            ->orderBy('hora_entrada')
            ->get();

        $todasFechas = Asistencia::selectRaw('DISTINCT fecha')
            ->orderByDesc('fecha')
            ->pluck('fecha');

        return view('asistencia.index', compact('registros', 'todasFechas', 'fecha'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $nombre = strtoupper(trim($validated['nombre']));

        $registro = Asistencia::create([
            'nombre' => $nombre,
            'fecha' => now()->format('Y-m-d'),
            'hora_entrada' => now()->format('H:i:s'),
        ]);

        return redirect()->route('asistencia.index')
            ->with('status', "Entrada registrada: {$nombre} a las {$registro->hora_entrada->format('H:i')}");
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
            ->with('status', "Salida registrada: {$asistencia->nombre} a las {$asistencia->hora_salida->format('H:i')}");
    }

    public function destroy(Asistencia $asistencia)
    {
        $nombre = $asistencia->nombre;
        $asistencia->delete();

        return redirect()->route('asistencia.index')
            ->with('status', "Registro de {$nombre} eliminado.");
    }
}
