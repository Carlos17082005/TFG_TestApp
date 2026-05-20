<?php

namespace App\Services;

use App\Services\TestService;
use App\Models\Pregunta;
use App\Models\Test;
use Illuminate\Support\Collection;

class TestService
{
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Configuracion y aletorizacion de preguntas

    /**
     * Gestiona el subconjunto de preguntas y su ordenación basándose en
     * los parámetros configurados en el test (preguntas_a_mostrar y aleatorio).
     * Mantiene los datos fijados en sesión durante todo el intento.
     */
    public function prepararPreguntasTest(Test $test) {
        $keyIds = "test_{$test->id_test}_ids";

        if (!session()->has($keyIds)) {
            $preguntasBase = $test->preguntas; // Orden relativo original de la relación

            // 1. Gestionar 'preguntas_a_mostrar' (Sorteo de X preguntas sobre Y)
            if ($test->preguntas_a_mostrar && $test->preguntas_a_mostrar < $preguntasBase->count()) {
                $subconjunto = $preguntasBase->shuffle()->take($test->preguntas_a_mostrar);
                
                // 2. Gestionar 'aleatorio' si se extrajo un subconjunto
                if (!$test->aleatorio) {
                    // Si NO es aleatorio, restauramos el orden relativo original de la BD
                    $idsSubconjunto = $subconjunto->pluck('id_pregunta')->toArray();
                    $preguntasFinales = $preguntasBase->whereIn('id_pregunta', $idsSubconjunto)->values();
                } else {
                    $preguntasFinales = $subconjunto->values();
                }
            } else {
                // Si se muestran todas las preguntas asignadas
                if ($test->aleatorio) {
                    $preguntasFinales = $preguntasBase->shuffle()->values();
                } else {
                    $preguntasFinales = $preguntasBase->values();
                }
            }

            // Guardamos las IDs elegidas y su orden definitivo en la sesión
            session([$keyIds => $preguntasFinales->pluck('id_pregunta')->toArray()]);
        }

        // Reconstruimos la colección en base al orden estricto de la sesión
        $idsGuardados = session($keyIds);
        $todasPreguntas = $test->preguntas;

        return collect($idsGuardados)
            ->map(fn($id) => $todasPreguntas->firstWhere('id_pregunta', $id))
            ->filter()
            ->values();
    }

    /**
     * Aleatoriza las opciones internas de una pregunta y las guarda en sesión.
     */
    public function aleatorizarOpciones(Pregunta $pregunta): array {
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

    private function aleatorizarMultiple(array $contenido, string $key): array {
        $opciones = $contenido['opciones'];
        shuffle($opciones);
        session([$key => $opciones]);
        return array_merge($contenido, ['opciones' => $opciones]);
    }

    private function aleatorizarConecta(array $contenido, string $key): array {
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

    private function aplicarOrden(string $tipo, array $contenido, mixed $guardado): array {
        return match ($tipo) {
            'multiple' => array_merge($contenido, ['opciones' => $guardado]),
            'conecta'  => array_merge($contenido, [
                'columna_b_mezclada' => is_array($guardado) && isset($guardado['columna_b']) ? $guardado['columna_b'] : $guardado,
                'parejas'            => is_array($guardado) && isset($guardado['parejas']) ? $guardado['parejas'] : $contenido['parejas']
            ]),
            default    => $contenido,
        };
    }

    public function limpiarSesion(int $idTest, Collection $preguntas): void {
        // Limpiamos la nueva clave de control de IDs seleccionadas
        session()->forget("test_{$idTest}_ids");

        foreach ($preguntas as $pregunta) {
            session()->forget("test_interno_{$pregunta->id_pregunta}");
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Correccion 

    public function corregir(array $respuestas, Collection $preguntas): array {
        $aciertos = 0.0;
        $informe  = [];

        foreach ($preguntas as $pregunta) {
            $id        = $pregunta->id_pregunta;
            $tipo      = $pregunta->tipo;
            $contenido = $pregunta->contenido->toArray();

            $respUsuario  = $respuestas[$id] ?? null;
            $respCorrecta = match ($tipo) {
                'balance' => $contenido['secciones'] ?? null,
                'conecta' => null,
                default   => $contenido['respuesta'] ?? null,
            };

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

    private function corregirPregunta(string $tipo, array $contenido, mixed $respUsuario, mixed $respCorrecta): float {
        if ($tipo === 'balance') {
            return $this->corregirBalance($respUsuario, $contenido['secciones'] ?? []);
        }
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

    private function corregirExacto(mixed $usuario, mixed $correcta): float {
        return (string)$usuario === (string)$correcta ? 1.0 : 0.0;
    }

    private function corregirTextoNormalizado(mixed $usuario, mixed $correcta): float {
        return $this->normalizar((string)$usuario) === $this->normalizar((string)$correcta) ? 1.0 : 0.0;
    }

    private function corregirConecta(mixed $usuario, array $parejas): float {
        if (!is_array($usuario) || empty($parejas)) return 0.0;
        $total = count($parejas); $aciertos = 0;
        foreach ($parejas as $index => $pareja) {
            $seleccionado = $usuario[$index] ?? null;
            if ($this->normalizar((string)$seleccionado) === $this->normalizar($pareja['b'])) { $aciertos++; }
        }
        return $total > 0 ? $aciertos / $total : 0.0;
    }

    private function corregirBalance(mixed $usuario, array $secciones): float {
        if (!is_array($usuario) || empty($secciones)) return 0.0;
        $correctos = [];
        foreach ($secciones as $sec) {
            foreach ($sec['bloques'] as $bi => $bloque) {
                foreach ($bloque['filas'] as $fila) {
                    if (empty($fila['nombre'])) continue;
                    $correctos[strtolower(trim($fila['nombre']))] = [
                        'secKey'  => $sec['key'],
                        'bi'      => $bi,
                        'importe' => $fila['importe'] ?? '',
                    ];
                }
            }
        }
        $total = count($correctos); $aciertos = 0;
        foreach ($usuario as $secKey => $bloques) {
            foreach ($bloques as $bi => $filas) {
                foreach ($filas as $filaU) {
                    $nombre = strtolower(trim($filaU['nombre'] ?? ''));
                    if (empty($nombre)) continue;
                    $correcto = $correctos[$nombre] ?? null;
                    if (!$correcto) continue;
                    $secOk     = $correcto['secKey'] === $secKey;
                    $bloqueOk  = (int) $correcto['bi'] === (int) $bi;
                    $importeOk = abs($this->normalizarImporte((string) ($filaU['importe'] ?? '')) - $this->normalizarImporte((string) $correcto['importe'])) < 0.01;
                    if ($secOk && $bloqueOk && $importeOk) { $aciertos++; }
                }
            }
        }
        return $total > 0 ? $aciertos / $total : 0.0;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Utilidades 

    public function normalizar(string $texto): string {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $texto = strtr($texto, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ä'=>'a','ë'=>'e','ï'=>'i','ö'=>'o','ü'=>'u','à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u',"'"=>"'","'"=>"'",'¿'=>'','¡'=>'']);
        return preg_replace('/\s+/', ' ', $texto);
    }

    private function normalizarImporte(string $valor): float {
        $valor = trim($valor);
        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor); $valor = str_replace(',', '.', $valor);
        } else {
            if (preg_match('/^\d{1,3}\.\d{3}$/', $valor)) { $valor = str_replace('.', '', $valor); }
        }
        return (float) preg_replace('/[^\d.]/', '', $valor);
    }
}