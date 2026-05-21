<?php

namespace App\Services;

use App\Models\Pregunta;
use App\Models\Etiqueta;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PreguntaService
{
    public function crearPregunta(Request $request, $id_modulo) {
        [$validated, $contenido, $etiquetas] = $this->prepararDatos($request, $id_modulo);

        if ($request->hasFile('audio')) {
            $path = $request->file('audio')->store('audios/preguntas', 'public');
            $contenido['audio_path'] = $path;
            $contenido['audio_mime'] = $request->file('audio')->getMimeType();
        }

        return DB::transaction(function () use ($validated, $contenido, $id_modulo, $etiquetas) {
            $pregunta = Pregunta::create([
                'tipo'      => $validated['tipo'],
                'contenido' => $contenido,
                'id_modulo' => $id_modulo,
            ]);

            if (!empty($etiquetas)) {
                $pregunta->listaEtiquetas()->sync($etiquetas);
            }

            return $pregunta;
        });
    }

    public function actualizarPregunta(Request $request, Pregunta $pregunta) {
        // Obtenemos el id_modulo desde la propia pregunta para pasárselo a prepararDatos
        [$validated, $contenido, $etiquetas] = $this->prepararDatos($request, $pregunta->id_modulo);

        $oldAudioPath = $pregunta->contenido['audio_path'] ?? null;

        if ($request->boolean('eliminar_audio') && $oldAudioPath) {
            Storage::disk('public')->delete($oldAudioPath);

        } elseif ($request->hasFile('audio')) {
            $path = $request->file('audio')->store('audios/preguntas', 'public');
            $contenido['audio_path'] = $path;
            $contenido['audio_mime'] = $request->file('audio')->getMimeType();

            if ($oldAudioPath && Storage::disk('public')->exists($oldAudioPath)) {
                Storage::disk('public')->delete($oldAudioPath);
            }
        } else {
            if ($oldAudioPath) {
                $contenido['audio_path'] = $oldAudioPath;
            }
        }

        return DB::transaction(function () use ($pregunta, $validated, $contenido, $etiquetas) {
            $pregunta->update([
                'tipo'      => $validated['tipo'],
                'contenido' => $contenido,
            ]);

            if (!empty($etiquetas)) {
                $pregunta->listaEtiquetas()->sync($etiquetas);
            }

            return $pregunta;
        });
    }

    /**
     * Valida el request y construye el array $contenido según el tipo de pregunta.
     * Recibe $id_modulo para crear las etiquetas nuevas dentro del módulo correcto.
     */
    private function prepararDatos(Request $request, int $id_modulo) {
        $validated = $request->validate([
            'tipo'      => 'required|string|max:255',
            'enunciado' => 'required|string|max:255',

            // Múltiple
            'opciones'   => 'nullable|required_if:tipo,multiple|array|min:3',
            'opciones.*' => 'nullable|required_if:tipo,multiple|string|max:255',

            // Conecta
            'columna_a'   => 'nullable|required_if:tipo,conecta|array|min:2',
            'columna_a.*' => 'nullable|required_if:tipo,conecta|string|max:255',
            'columna_b'   => 'nullable|required_if:tipo,conecta|array|min:2',
            'columna_b.*' => 'nullable|required_if:tipo,conecta|string|max:255',

            // Balance
            'secciones' => 'nullable|required_if:tipo,balance|string',

            // Respuesta (no aplica a conecta ni balance)
            'respuesta' => 'required_unless:tipo,conecta,balance|nullable|string|max:255',

            // Etiquetas
            'etiquetas_existentes'   => 'nullable|array',
            'etiquetas_existentes.*' => 'integer|exists:etiquetas,id_etiqueta',
            'etiquetas_nuevas'       => 'nullable|array',
            'etiquetas_nuevas.*'     => 'string|max:255',

            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
        ]);

        // Construir $contenido según el tipo
        if ($validated['tipo'] === 'multiple') {
            $contenido = [
                'enunciado' => $validated['enunciado'],
                'opciones'  => $validated['opciones'],
                'respuesta' => $validated['respuesta'],
            ];

        } elseif ($validated['tipo'] === 'conecta') {
            $parejas = [];
            foreach ($validated['columna_a'] as $index => $valorA) {
                $parejas[] = [
                    'a' => $valorA,
                    'b' => $validated['columna_b'][$index],
                ];
            }
            $contenido = [
                'enunciado' => $validated['enunciado'],
                'parejas'   => $parejas,
            ];

        } elseif ($validated['tipo'] === 'balance') {
            $secciones = json_decode($request->input('secciones'), true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($secciones)) {
                throw new \InvalidArgumentException('El formato del balance no es válido.');
            }

            foreach ($secciones as &$sec) {
                foreach ($sec['bloques'] as &$bloque) {
                    $bloque['filas'] = array_values(
                        array_filter($bloque['filas'], fn($f) => !empty($f['nombre']))
                    );
                }
            }
            unset($sec, $bloque);

            $contenido = [
                'enunciado' => $validated['enunciado'],
                'secciones' => $secciones,
            ];

        } else {
            // booleana, texto
            $contenido = [
                'enunciado' => $validated['enunciado'],
                'respuesta' => $validated['respuesta'],
            ];
        }

        // Etiquetas: solo las del módulo actual
        $etiquetas = [];

        if ($request->has('etiquetas_existentes')) {
            // Verificamos que las etiquetas existentes pertenecen al módulo actual
            // para evitar que alguien inyecte ids de otros módulos
            $etiquetasValidas = Etiqueta::where('id_modulo', $id_modulo)
                ->whereIn('id_etiqueta', $request->etiquetas_existentes)
                ->pluck('id_etiqueta')
                ->toArray();

            $etiquetas = $etiquetasValidas;
        }

        if ($request->has('etiquetas_nuevas')) {
            foreach ($request->etiquetas_nuevas as $nombreEtiqueta) {
                $nombre = strtolower(trim($nombreEtiqueta));

                // firstOrCreate con id_modulo: la misma etiqueta puede existir
                // en otro módulo sin conflicto gracias al índice único (id_modulo, nombre)
                $etiqueta = Etiqueta::firstOrCreate(
                    ['id_modulo' => $id_modulo, 'nombre' => $nombre]
                );

                $etiquetas[] = $etiqueta->id_etiqueta;
            }
        }

        return [$validated, $contenido, $etiquetas];
    }

    public function redirigir(Modulo $modulo) {
        $borrador = session(\App\Http\Controllers\PreguntaController::SESSION_KEY);

        if ($borrador && ($borrador['origen_modulo'] ?? null) === $modulo->id_modulo) {
            $idTest = $borrador['origen_test'] ?? null;

            return $idTest
                ? redirect()->route('profesor.tests.edit', [$modulo->id_modulo, $idTest])
                : redirect()->route('profesor.tests.create', $modulo->id_modulo);
        }

        return redirect()->route('profesor.preguntas.index', $modulo->id_modulo);
    }
}