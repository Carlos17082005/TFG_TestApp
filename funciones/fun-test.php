<?php
    include 'decode.php';

    function preguntasTest($modulo, $id_test)  {  
        try  {
            $conn = conexionBD();
            $stmt = $conn->prepare("select p.id_pregunta, contenido from preguntas p, preguntas_tests pt where p.id_pregunta = pt.id_pregunta and modulo = (:modulo) and id_test = (:id_test);");
            $stmt->bindParam(':modulo', $modulo);
            $stmt->bindParam(':id_test', $id_test);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            return $stmt->fetchAll();

        }  catch  (PDOException $e)  {
            throw $e;
        }  finally  {
            if ($conn !== null) { $conn = null; }
        }
    }

    function mostrarTest($preguntas, $informe = null) {  
        echo '<form name="respuestas" method="POST" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
        
        foreach ($preguntas as $row) {
            $id_pregunta = $row['id_pregunta'];
            $contenido = json_decode($row['contenido'], true);  
            $estado = $informe ? $informe[$id_pregunta] : null;
            
            echo '<div class="question-card">';
            if (is_string($contenido['pregunta']))  {
                echo '<h3>' . htmlspecialchars($contenido['pregunta']) . '</h3>';
            }
            
            if (isset($contenido['tipo']))  {
                switch ($contenido['tipo']) {
                    case 'tf': decode_tf($id_pregunta, $contenido, $estado); break;
                    case 'conecta': decode_conecta($id_pregunta, $contenido, $estado); break;
                    case 'texto': decode_texto($id_pregunta, $contenido, $estado); break;
                    case 'number': decode_number($id_pregunta, $contenido, $estado); break;
                }

            } else {
                if (!$informe) {
                    $letras = array_keys($contenido['opciones']);
                    
                    // --- INTERRUPTOR APLICADO AQUÍ ---
                    if (ACTIVAR_ALEATORIEDAD) {
                        shuffle($letras); 
                    }
                    
                    $_SESSION['orden_internos'][$id_pregunta] = $letras; 
                } else {
                    $letras = $_SESSION['orden_internos'][$id_pregunta] ?? array_keys($contenido['opciones']);
                }

                $index_letra = 0; 
                foreach ($letras as $letra) {
                    $texto_opcion = $contenido['opciones'][$letra];
                    $letra_visual = chr(97 + $index_letra); 
                    
                    $class = 'option-label';
                    $checked = '';
                    $disabled = $informe ? 'disabled' : '';

                    if ($estado) {
                        if ($estado['usuario'] === $letra) $checked = 'checked';
                        
                        if ($estado['correcta'] === $letra) {
                            $class .= ' correct-bg'; 
                        } elseif ($estado['usuario'] === $letra) {
                            $class .= ' incorrect-bg'; 
                        }
                    }

                    echo '<label class="'.$class.'">';
                    echo '<input type="radio" name="respuestas['.$id_pregunta.']" value="'.$letra.'" '.$disabled.' '.$checked.' ' . (!$informe ? 'required' : '') . '> ';
                    echo htmlspecialchars($letra_visual . ') ' . $texto_opcion);
                    echo '</label>';
                    
                    $index_letra++;
                }
            }
            echo '</div>';
        }
        
        if (!$informe) {
            echo '<button type="submit" name="enviar_test">Finalizar y Evaluar</button>';
        }
        echo '</form>';
    }

    function comprobarRespuestas($rUsuario, $preguntas) {  
        $aciertos = 0;
        $tp = count($preguntas);  
        $informe = [];

        foreach ($preguntas as $row) {
            $id_pregunta = $row['id_pregunta'];
            $contenido = json_decode($row['contenido'], true);  
            $respuesta_correcta = $contenido['respuesta']; 
            $respuesta_usuario = isset($rUsuario[$id_pregunta]) ? $rUsuario[$id_pregunta] : null;  

            $informe[$id_pregunta] = [
                'correcta' => $respuesta_correcta,
                'usuario' => $respuesta_usuario
            ];

            if (isset($contenido['tipo']) && $contenido['tipo'] === 'conecta') {
                if (is_array($respuesta_usuario)) {
                    $pares_correctos = 0;
                    $total_pares = count($respuesta_correcta); 
                    
                    foreach ($respuesta_correcta as $numero => $letra) {
                        if (isset($respuesta_usuario[$numero]) && $respuesta_usuario[$numero] === $letra) {
                            $pares_correctos++;
                        }
                    }
                    $aciertos += ($pares_correctos / $total_pares);
                }
                
            } else if (isset($contenido['tipo']) && $contenido['tipo'] === 'texto')  {
                if (normalizarRespuesta($respuesta_usuario) === normalizarRespuesta($respuesta_correcta)) { 
                    $aciertos++; 
                }

            } else {  
                if ($respuesta_usuario === $respuesta_correcta) { 
                    $aciertos++; 
                }
            }
        }

        $nota_final = ($tp > 0) ? round(($aciertos / $tp) * 100) : 0;
        return ['aciertos' => $aciertos, 'total' => $tp, 'nota_final' => $nota_final, 'informe' => $informe];
    }
?>