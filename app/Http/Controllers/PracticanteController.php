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
        ]);

        $nombre = strtoupper(trim($validated['nombre']));

        Practicante::create([
            'nombre' => $nombre,
        ]);

        return redirect()->route('asistencia.index')->with('status', 'Practicante registrado exitosamente.');
    }
}
