<?php
    include 'decode.php';

    function preguntasTest($modulo, $id_test)  {  // Recupera las preguntas de un test de la base de datos
    // Recibe el modulo y el id test para poder identificar correctamente el test que quiere realizar
    // Devuelve un array con todas las preguntas de un test
        try  {
            $conn = conexionBD();
            $stmt = $conn->prepare("select p.id_pregunta, contenido from preguntas p, preguntas_tests pt where p.id_pregunta = pt.id_pregunta and modulo = (:modulo) and id_test = (:id_test);");
            $stmt->bindParam(':modulo', $modulo);
            $stmt->bindParam(':id_test', $id_test);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $resultado=$stmt->fetchAll();
            return($resultado);

        }  catch  (PDOException $e)  {
            throw $e;

        }  finally  {
            if ($conn !== null) { $conn = null; }
        }
    }

    function mostrarTest($preguntas) {  // Crea un formulario con todas las preguntas
    // Recibe un array con las preguntas y lo imprime por pantalla
        echo '<form name="respuestas" method="POST" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
        
        foreach ($preguntas as $row) {
            $id_pregunta = $row['id_pregunta'];
            $contenido = json_decode($row['contenido'], true);  // Convierte un JSON en un array asociativo
            
            echo '<div class="question-card">';
            if (is_string($contenido['pregunta']))  {
                echo '<h3>' . htmlspecialchars($contenido['pregunta']) . '</h3>';
            }
            
            // Aqui se crean las preguntas, si queremos añadir mas tipos hay que cambiar esto
            // ---------------------------------------------------------------------------------
            // -- Tipo de preguntas -------------------------
            // de momento la preguntas sin tipo son tipo test -> hay que camiarlo y ponerles tipo test
            // tf: Verdadero o falso (true or false)   --->  idea: (en el documento)
            // conecta: Pregunta con 2 columnas que tienes que unir   --->  idea: (en el documento)
            // texto: Completar el hueco con una palabra o frase
            // number: Preguntas matematicas
            if (isset($contenido['tipo']))  {
                switch ($contenido['tipo']) {
                    case 'tf':
                        decode_tf($id_pregunta, $contenido);
                        break;
                    case 'conecta':
                        decode_conecta($id_pregunta, $contenido);
                        break;
                    case 'texto':
                        decode_texto($id_pregunta, $contenido);
                        break;
                    case 'number':
                        decode_number($id_pregunta, $contenido);
                        break;
                }

            } else {
                foreach ($contenido['opciones'] as $letra => $texto_opcion) {
                    echo '<label class="option-label">';
                    echo '<input type="radio" name="respuestas[' . $id_pregunta . ']" value="' . $letra . '" required> ';
                    echo htmlspecialchars($letra . ') ' . $texto_opcion);
                    echo '</label>';
                }
            }
            // ---------------------------------------------------------------------------------
            echo '</div>';
        }
        
        echo '<button type="submit" name="enviar_test">Finalizar y Evaluar</button>';
        echo '</form>';
    }

    function comprobarRespuestas($rUsuario, $preguntas) {  // Calcula la puntacion obtenida en el test
        // Recibe un array con las preguntas (donde esta la solucion) y un array con las respuestas del usuario
        // Devuelve un array asosiativo con los aciertos, el total de preguntas acertadas y la nota final 
        $aciertos = 0;
        $tp = count($preguntas);  // Total preguntas

        foreach ($preguntas as $row) {
            $id_pregunta = $row['id_pregunta'];
            $contenido = json_decode($row['contenido'], true);  // Convierte un JSON en un array asociativo
            $respuesta_correcta = $contenido['respuesta']; 
            $respuesta_usuario = isset($rUsuario[$id_pregunta]) ? $rUsuario[$id_pregunta] : null;  // Si no hay una respuesta del usuario pone null


// REVISAR
///////////////////////////////////////////////////////////////////////////////////////////////////////
                        // Comprobamos el TIPO de pregunta para evaluar distinto
                        if (isset($contenido['tipo']) && $contenido['tipo'] === 'conecta') {
                            
                            // LÓGICA PREGUNTA CONECTA (Puntuación Parcial)
                            if (is_array($respuesta_usuario)) {
                                $pares_correctos = 0;
                                $total_pares = count($respuesta_correcta); // Ej: 3 conexiones posibles
                                
                                // $respuesta_correcta tiene formato ["a" => "1", "b" => "2", "c" => "79"]
                                foreach ($respuesta_correcta as $numero => $letra) {
                                    
                                    // Comprobamos si el usuario en el select número "1" eligió la letra "a"
                                    if (isset($respuesta_usuario[$numero]) && $respuesta_usuario[$numero] === $letra) {
                                        $pares_correctos++;
                                    }
                                }
                                
                                // Sumamos la fracción correspondiente (Ej: 2 correctas de 3 = +0.66 aciertos)
                                $aciertos += ($pares_correctos / $total_pares);
                            }
                            
                        } else if (isset($contenido['tipo']) && $contenido['tipo'] === 'text')  {
                            if (normalizarRespuesta($respuesta_usuario) === normalizarRespuesta($respuesta_correcta)) { 
                                $aciertos++; 
                            }

                        } else {  // Podriamos unificarlas con las de tipo texto pero no me da tiempo de revisar todo bien para ver si genera conflictos
                            // LÓGICA PREGUNTAS NORMALES (Tipo Test y V/F)
                            if ($respuesta_usuario === $respuesta_correcta) { 
                                $aciertos++; 
                            }
                        }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

        }

        $nota_final = ($tp > 0) ? round(($aciertos / $tp) * 100) : 0;

        // Falta hacer el insert en la BD
        // GuardarRespuestas() {}
        return ['aciertos' => $aciertos, 'total' => $tp, 'nota_final' => $nota_final,];
    }
?>