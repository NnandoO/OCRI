<?php
$content = file_get_contents('app/Http/Controllers/AsistenciaController.php');
$method = <<<'PHP'
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
PHP;

$content = str_replace("}\n", $method . "\n", $content);
file_put_contents('app/Http/Controllers/AsistenciaController.php', $content);
