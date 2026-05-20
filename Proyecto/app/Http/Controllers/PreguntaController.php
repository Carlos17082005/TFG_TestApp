<?php

namespace App\Http\Controllers;

use App\Models\Pregunta;
use App\Models\Modulo;
use App\Models\Etiqueta;
use App\Services\PreguntaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;  // para borrar audios

class PreguntaController extends Controller
{
    const SESSION_KEY = 'test_borrador';
    public function __construct(protected PreguntaService $preguntaService) {}

    public function index(Modulo $modulo) {
        session()->forget(self::SESSION_KEY);
        $preguntas = $modulo->preguntas()->with('listaEtiquetas')->get();
        return view('usuario.profesor.preguntas.preguntas', compact('preguntas', 'modulo'));
    }

    public function create(Modulo $modulo) {
        $etiquetas_bd = Etiqueta::all();
        return view('usuario.profesor.preguntas.gestionPregunta', compact('modulo', 'etiquetas_bd'));
    }

    public function store(Request $request, Modulo $modulo) {        
        try {
            $this->preguntaService->crearPregunta($request, $modulo->id_modulo);

            return $this->preguntaService->redirigir($modulo);
            
        } catch(\Exception $e) {
           return back()->withErrors(['error' => 'Error al crear la pregunta, inténtalo de nuevo.']);
        }        
    }

    public function edit(Modulo $modulo, Pregunta $pregunta) {
        $etiquetas_bd = Etiqueta::all();
        $pregunta->load('listaEtiquetas');
        return view('usuario.profesor.preguntas.gestionPregunta', compact('modulo', 'etiquetas_bd', 'pregunta'));
    }

    public function update(Request $request, Modulo $modulo, Pregunta $pregunta) {
        try {
            $this->preguntaService->actualizarPregunta($request, $pregunta);

            return $this->preguntaService->redirigir($modulo);
            
        } catch(\Exception $e) {
           return back()->withErrors(['error' => 'Error al actualizar la pregunta, inténtalo de nuevo.' . $e->getMessage()]);
        } 
    }

    public function destroy(Modulo $modulo, Pregunta $pregunta) {
        try {
            $audioPath = $pregunta->contenido['audio_path'] ?? null;
            $pregunta->delete();

            // borramos el archivo del servidor
            if ($audioPath && Storage::disk('public')->exists($audioPath)) {
                Storage::disk('public')->delete($audioPath);
            }

            return redirect()->route('profesor.preguntas.index', $modulo->id_modulo);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al borrar la pregunta, inténtalo de nuevo.']);
        }
    }
}

