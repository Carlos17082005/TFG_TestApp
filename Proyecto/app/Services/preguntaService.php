<?php

namespace App\Services;

use App\Models\Pregunta;
use App\Models\Etiqueta;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;  // para borrar audios

class PreguntaService
{
    public function crearPregunta(Request $request, $id_modulo) {
        [$validated, $contenido, $etiquetas] = $this->prepararDatos($request);

        // Subir nuevo audio (usa mezcladora de nombres para evitar conflictos si se suben 2 audios con el mismo nombre)
        if ($request->hasFile('audio')) {
            $path = $request->file('audio')->store('audios/preguntas', 'public');
            $contenido['audio_path'] = $path;
            $contenido['audio_mime'] = $request->file('audio')->getMimeType();
        }

        // Usamos una transacción por si falla algo al guardar las etiquetas, que no se guarde la pregunta a medias
        return DB::transaction(function () use ($validated, $contenido, $id_modulo, $etiquetas) {
            $pregunta = Pregunta::create([
                'tipo' => $validated['tipo'],
                'contenido' => $contenido,
                'id_modulo' => $id_modulo
            ]);

            if (!empty($etiquetas)) {
                $pregunta->listaEtiquetas()->sync($etiquetas);
            }

            return $pregunta;
        });
    }

    public function actualizarPregunta(Request $request, Pregunta $pregunta) {
        [$validated, $contenido, $etiquetas] = $this->prepararDatos($request);

        // Mantener la referencia del audio viejo
        $oldAudioPath = $pregunta->contenido['audio_path'] ?? null;

        if ($request->boolean('eliminar_audio') && $oldAudioPath) {
            Storage::disk('public')->delete($oldAudioPath);

        // Subir nuevo audio (usa mezcladora de nombres para evitar conflictos si se suben 2 audios con el mismo nombre)
        } elseif ($request->hasFile('audio')) {
            // Guardar el nuevo
            $path = $request->file('audio')->store('audios/preguntas', 'public');
            $contenido['audio_path'] = $path;
            $contenido['audio_mime'] = $request->file('audio')->getMimeType();

            // Eliminamos el audio antiguo (Limpieza)
            if ($oldAudioPath && Storage::disk('public')->exists($oldAudioPath)) {
                Storage::disk('public')->delete($oldAudioPath);
            }
        } else {
            // Si no sube audio nuevo, se mantiene el viejo en el JSON
            if ($oldAudioPath) {
                $contenido['audio_path'] = $oldAudioPath;
            }
        }

        // Usamos una transacción por si falla algo al guardar las etiquetas, que no se guarde la pregunta a medias
        return DB::transaction(function () use ($pregunta, $validated, $contenido, $etiquetas) {
            $pregunta->update([
                'tipo' => $validated['tipo'],
                'contenido' => $contenido,
            ]);

            if (!empty($etiquetas)) {
                $pregunta->listaEtiquetas()->sync($etiquetas);
            }

            return $pregunta;
        });
    }

    private function prepararDatos(Request $request) {
        $validated = $request->validate([
            'tipo' => 'required|string|max:255',
            'enunciado' => 'required|string|max:255',

            'opciones' => 'nullable|required_if:tipo,multiple|array|min:3',
            'opciones.*' => 'nullable|required_if:tipo,multiple|string|max:255',

            'columna_a' => 'nullable|required_if:tipo,conecta|array|min:2',
            'columna_a.*' => 'nullable|required_if:tipo,conecta|string|max:255',
            'columna_b' => 'nullable|required_if:tipo,conecta|array|min:2',
            'columna_b.*' => 'nullable|required_if:tipo,conecta|string|max:255',

            'respuesta' => 'required_unless:tipo,conecta|string|max:255',

            'etiquetas_existentes'   => 'nullable|array',
            'etiquetas_existentes.*' => 'integer|exists:etiquetas,id_etiqueta',
            
            'etiquetas_nuevas'       => 'nullable|array',
            'etiquetas_nuevas.*'     => 'string|max:255',

            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240', // 10MB máx
        ]);

        if ($validated['tipo'] == 'multiple') {
            $contenido = [
                'enunciado' => $validated['enunciado'],
                'opciones' => $validated['opciones'],
                'respuesta' => $validated['respuesta']
            ];
        } else if ($validated['tipo'] == 'conecta') {
            $parejas = [];
            foreach ($validated['columna_a'] as $index => $valorA) {
                $parejas[] = [
                    'a' => $valorA,
                    'b' => $validated['columna_b'][$index]
                ];
            }
            $contenido = [
                'enunciado' => $validated['enunciado'],
                'parejas'   => $parejas
            ];
        } else {
            $contenido = [
                'enunciado' => $validated['enunciado'],
                'respuesta' => $validated['respuesta']
            ];
        }

        // ETIQUETAS
        $etiquetas = [];

        if ($request->has('etiquetas_existentes')) {
            $etiquetas = $request->etiquetas_existentes;
        }

        if ($request->has('etiquetas_nuevas')) {
            foreach ($request->etiquetas_nuevas as $nombreEtiqueta) {
                $etiqueta = Etiqueta::firstOrCreate([
                    'nombre' => strtolower(trim($nombreEtiqueta))
                ]);
                $etiquetas[] = $etiqueta->id_etiqueta;
            }
        }

        return [$validated, $contenido, $etiquetas];
    }

    public function redirigir(Modulo $modulo) {
        $borrador = session(\App\Http\Controllers\PreguntaController::SESSION_KEY);

        if ($borrador && ($borrador['origen_modulo'] ?? null) === $modulo->id_modulo) {
            $idTest = $borrador['origen_test'] ?? null;

            if ($idTest) {
                return redirect()->route('profesor.tests.edit', [$modulo->id_modulo, $idTest]);
            }
            return redirect()->route('profesor.tests.create', $modulo->id_modulo);
        }
        return redirect()->route('profesor.preguntas.index', $modulo->id_modulo);
    }
}