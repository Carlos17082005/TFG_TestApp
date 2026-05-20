<?php

namespace App\Services;

use App\Models\Pregunta;
use Illuminate\Support\Collection;

class TestService
{
    // =========================================================================
    // ALEATORIZACIÓN
    // =========================================================================

    /**
     * Devuelve las preguntas en orden aleatorio, guardando ese orden en sesión
     * para que la vista de resultados pueda mostrarlas en el mismo orden.
     * Si ya existe el orden (navegación atrás), lo reutiliza.
     */
    public function aleatorizarPreguntas(Collection $preguntas, int $idTest): Collection
    {
        $key = "test_{$idTest}_orden";

        if (!session()->has($key)) {
            $ids = $preguntas->pluck('id_pregunta')->shuffle()->toArray();
            session([$key => $ids]);
        }

        $ids = session($key);

        return collect($ids)
            ->map(fn($id) => $preguntas->firstWhere('id_pregunta', $id))
            ->filter()
            ->values();
    }

    /**
     * Aleatoriza las opciones internas de una pregunta y las guarda en sesión.
     * Devuelve el array de contenido listo para pasar a la vista.
     *
     * - multiple  → baraja el array de opciones
     * - conecta   → baraja la columna B (los A siempre en orden)
     * - booleana/texto → sin cambios
     */
    public function aleatorizarOpciones(Pregunta $pregunta): array
    {
        $id        = $pregunta->id_pregunta;
        $tipo      = $pregunta->tipo;
        $contenido = $pregunta->contenido->toArray();
        $key       = "test_interno_{$id}";

        if (session()->has($key)) {
            return $this->aplicarOrden($tipo, $contenido, session($key));
        }

        return match ($tipo) {
            'multiple' => $this->aleatorizarMultiple($contenido, $key),
            'conecta'  => $this->aleatorizarConecta($contenido, $key),
            default    => $contenido,
        };
    }

    private function aleatorizarMultiple(array $contenido, string $key): array
    {
        $opciones = $contenido['opciones'];
        shuffle($opciones);
        session([$key => $opciones]);
        return array_merge($contenido, ['opciones' => $opciones]);
    }

    private function aleatorizarConecta(array $contenido, string $key): array
    {
        $columnB = collect($contenido['parejas'])->pluck('b')->shuffle()->toArray();

        $parejas = $contenido['parejas'];
        $claves = array_keys($parejas);
        
        shuffle($claves);
        
        $parejasMezcladas = [];
        foreach ($claves as $clave) {
            $parejasMezcladas[$clave] = $parejas[$clave];
        }

        session([$key => [
            'columna_b' => $columnB,
            'parejas'   => $parejasMezcladas
        ]]);

        return array_merge($contenido, [
            'columna_b_mezclada' => $columnB,
            'parejas'            => $parejasMezcladas
        ]);
    }

    private function aplicarOrden(string $tipo, array $contenido, mixed $guardado): array
    {
        return match ($tipo) {
            'multiple' => array_merge($contenido, ['opciones' => $guardado]),
            'conecta'  => array_merge($contenido, [
                // Ajustamos para leer el nuevo formato del array guardado en sesión [columna_b y parejas]
                'columna_b_mezclada' => is_array($guardado) && isset($guardado['columna_b']) ? $guardado['columna_b'] : $guardado,
                'parejas'            => is_array($guardado) && isset($guardado['parejas']) ? $guardado['parejas'] : $contenido['parejas']
            ]),
            default    => $contenido,
        };
    }

    public function limpiarSesion(int $idTest, Collection $preguntas): void
    {
        session()->forget("test_{$idTest}_orden");

        foreach ($preguntas as $pregunta) {
            session()->forget("test_interno_{$pregunta->id_pregunta}");
        }
    }


    // =========================================================================
    // CORRECCIÓN
    // =========================================================================

    /**
     * Corrige todas las respuestas y devuelve el informe completo.
     *
     * @param  array      $respuestas   $_POST['respuestas'] → [id_pregunta => respuesta]
     * @param  Collection $preguntas
     * @return array{
     *   nota: float,
     *   aciertos: float,
     *   total: int,
     *   informe: array
     * }
     */
    public function corregir(array $respuestas, Collection $preguntas): array
    {
        $aciertos = 0.0;
        $informe  = [];

        foreach ($preguntas as $pregunta) {
            $id        = $pregunta->id_pregunta;
            $tipo      = $pregunta->tipo;
            $contenido = $pregunta->contenido->toArray();

            $respUsuario  = $respuestas[$id] ?? null;
            $respCorrecta = $contenido['respuesta'] ?? null;

            $puntuacion = $this->corregirPregunta($tipo, $contenido, $respUsuario, $respCorrecta);
            $aciertos  += $puntuacion;

            $informe[$id] = [
                'tipo'       => $tipo,
                'correcta'   => $respCorrecta,
                'usuario'    => $respUsuario,
                'puntuacion' => $puntuacion,
            ];
        }

        $total = $preguntas->count();
        $nota  = $total > 0 ? round(($aciertos / $total) * 10, 2) : 0.0;

        return [
            'nota'     => $nota,
            'aciertos' => $aciertos,
            'total'    => $total,
            'informe'  => $informe,
        ];
    }

    private function corregirPregunta(string $tipo, array $contenido, mixed $respUsuario, mixed $respCorrecta): float
    {
        if ($respUsuario === null || $respUsuario === '') {
            return 0.0; 
        }
        
        return match ($tipo) {
            'multiple' => $this->corregirTextoNormalizado($respUsuario, $respCorrecta),
            'booleana' => $this->corregirExacto($respUsuario, $respCorrecta),
            'texto'    => $this->corregirTextoNormalizado($respUsuario, $respCorrecta),
            'conecta'  => $this->corregirConecta($respUsuario, $contenido['parejas']),
            default    => 0.0,
        };
    }

    /** Booleana: comparación exacta (verdadero/falso). */
    private function corregirExacto(mixed $usuario, mixed $correcta): float
    {
        return (string)$usuario === (string)$correcta ? 1.0 : 0.0;
    }

    /** Múltiple y texto: comparación normalizada. */
    private function corregirTextoNormalizado(mixed $usuario, mixed $correcta): float
    {
        return $this->normalizar((string)$usuario) === $this->normalizar((string)$correcta)
            ? 1.0
            : 0.0;
    }

    /**
     * Conecta: puntuación parcial según pares correctos.
     * El usuario envía [index => texto_b_seleccionado].
     */
    private function corregirConecta(mixed $usuario, array $parejas): float
    {
        if (!is_array($usuario) || empty($parejas)) return 0.0;

        $total    = count($parejas);
        $aciertos = 0;

        foreach ($parejas as $index => $pareja) {
            $seleccionado = $usuario[$index] ?? null;
            if ($this->normalizar((string)$seleccionado) === $this->normalizar($pareja['b'])) {
                $aciertos++;
            }
        }

        return $total > 0 ? $aciertos / $total : 0.0;
    }


    // =========================================================================
    // UTILIDADES
    // =========================================================================

    public function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $texto = strtr($texto, [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
            'ä'=>'a','ë'=>'e','ï'=>'i','ö'=>'o','ü'=>'u',
            'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u',
            "'"=>"'","'"=>"'",'¿'=>'','¡'=>'',
        ]);
        return preg_replace('/\s+/', ' ', $texto);
    }
}
