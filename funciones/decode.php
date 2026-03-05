<?php
    function decode_tf($id_pregunta, $contenido, $estado = null)  {
        $disabled = $estado ? 'disabled' : '';
        
        foreach (['True', 'False'] as $val) {
            $class = 'option-label';
            $checked = '';
            
            if ($estado) {
                if ($estado['usuario'] === $val) $checked = 'checked';
                if ($estado['correcta'] === $val) {
                    $class .= ' correct-bg';
                } elseif ($estado['usuario'] === $val) {
                    $class .= ' incorrect-bg';
                }
            }
            
            echo '<label class="'.$class.'"><input type="radio" name="respuestas['.$id_pregunta.']" value="'.$val.'" '.$disabled.' '.$checked.' '.(!$estado?'required':'').'> '.$val.'</label>';
        }
    }

    function decode_conecta($id_pregunta, $contenido, $estado = null)  {
        $disabled = $estado ? 'disabled' : '';

        if (!$estado) {
            $keys1 = array_keys($contenido['div-1']);
            $keys2 = array_keys($contenido['div-2']);
            $claves_select = array_keys($contenido['div-2']);

            // --- INTERRUPTOR APLICADO AQUÍ ---
            if (ACTIVAR_ALEATORIEDAD) {
                shuffle($keys1); 
                shuffle($keys2); 
                shuffle($claves_select);
            }

            $_SESSION['orden_internos'][$id_pregunta] = [
                'keys1' => $keys1, 
                'keys2' => $keys2,
                'claves_select' => $claves_select
            ];
        } else {
            $orden = $_SESSION['orden_internos'][$id_pregunta] ?? null;
            if ($orden) {
                $keys1 = $orden['keys1'];
                $keys2 = $orden['keys2'];
                $claves_select = $orden['claves_select'];
            } else {
                $keys1 = array_keys($contenido['div-1']);
                $keys2 = array_keys($contenido['div-2']);
                $claves_select = array_keys($contenido['div-2']);
            }
        }
        
        $map_numeros = [];
        foreach ($keys1 as $index => $orig_num) {
            $map_numeros[$orig_num] = $index + 1;
        }

        $map_letras = [];
        $display_to_original = [];
        foreach ($keys2 as $index => $orig_letra) {
            $disp_letra = chr(97 + $index); 
            $map_letras[$orig_letra] = $disp_letra;
            $display_to_original[$disp_letra] = $orig_letra;
        }
        
        ksort($display_to_original);

        $max_rows = max(count($keys1), count($keys2));

        echo '<div class="conecta-grid">'; 

        for ($i = 0; $i < $max_rows; $i++) {
            
            echo '<div class="conecta-item-izq">';
            if (isset($keys1[$i])) {
                $number = $keys1[$i];
                $texto_opcion = $contenido['div-1'][$number];
                $num_visual = $map_numeros[$number]; 
                echo '<strong>' . htmlspecialchars($num_visual) . '.</strong> ' . htmlspecialchars($texto_opcion);
            }
            echo '</div>';

            echo '<div class="conecta-select-centro">';
            if (isset($keys1[$i])) {
                $number = $keys1[$i];
                $classSelect = '';
                $user_ans = $estado ? ($estado['usuario'][$number] ?? '') : '';
                
                if ($estado) {
                    $correct_ans = $estado['correcta'][$number] ?? '';
                    if ($user_ans !== $correct_ans) {
                        $classSelect = 'incorrect-bg'; 
                    }
                }

                echo '<select name="respuestas[' . $id_pregunta . '][' . $number . ']" class="' . $classSelect . '" ' . $disabled . ' ' . (!$estado?'required':'') . '>';
                echo '<option value="">-</option>';
                
                foreach ($display_to_original as $disp_letter => $orig_key)  {
                    $selected = ($user_ans === (string)$orig_key) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($orig_key) . '" ' . $selected . '>' . htmlspecialchars($disp_letter) . '</option>';
                }
                echo '</select>';
                
                if ($estado && $user_ans !== $correct_ans) {
                    $letra_correcta_visual = isset($map_letras[$correct_ans]) ? $map_letras[$correct_ans] : $correct_ans;
                    echo '<div class="correct-text" style="font-size: 0.8rem; margin-top: 4px;">Correcta: ' . htmlspecialchars($letra_correcta_visual) . '</div>';
                }
            }
            echo '</div>';

            echo '<div class="conecta-item-der">';
            if (isset($keys2[$i])) {
                $letra = $keys2[$i];
                $texto_opcion = $contenido['div-2'][$letra];
                $letra_visual = $map_letras[$letra]; 
                echo '<strong>' . htmlspecialchars($letra_visual) . '.</strong> ' . htmlspecialchars($texto_opcion);
            }
            echo '</div>';
        }

        echo '</div>'; 
    }

    function decode_texto($id_pregunta, $contenido, $estado = null)  {  
        $pregunta = $contenido['pregunta'];
        $disabled = $estado ? 'disabled' : '';
        $user_ans = $estado ? htmlspecialchars($estado['usuario']) : '';
        
        $class = 'input-text input-texto-inline';
        if ($estado) {
            if (normalizarRespuesta($estado['usuario']) === normalizarRespuesta($estado['correcta'])) {
                $class .= ' correct-bg';
            } else {
                $class .= ' incorrect-bg';
            }
        }

        echo '<h3 style="display:flex; align-items:center; flex-wrap:wrap; gap:10px;">';
        echo htmlspecialchars($pregunta['cadena1']);
        echo '<input type="text" class="' . $class . '" name="respuestas[' . $id_pregunta . ']" value="' . $user_ans . '" ' . $disabled . ' ' . (!$estado?'required':'') . ' autocomplete="off">';
        echo htmlspecialchars($pregunta['cadena2']);
        echo '</h3>';
        
        if ($estado && normalizarRespuesta($estado['usuario']) !== normalizarRespuesta($estado['correcta'])) {
            echo '<div class="correct-text">Respuesta correcta: ' . htmlspecialchars($estado['correcta']) . '</div>';
        }
    }

    function decode_number($id_pregunta, $contenido, $estado = null)  {
        $disabled = $estado ? 'disabled' : '';
        $user_ans = $estado ? htmlspecialchars($estado['usuario']) : '';
        
        $class = 'input-number';
        if ($estado) {
            if ((string)$estado['usuario'] === (string)$estado['correcta']) {
                $class .= ' correct-bg';
            } else {
                $class .= ' incorrect-bg';
            }
        }

        echo 'Escribe tu respuesta: <br>';
        echo '<input type="number" class="' . $class . '" name="respuestas[' . $id_pregunta . ']" value="' . $user_ans . '" ' . $disabled . ' ' . (!$estado?'required':'') . ' step="0.01">';
        
        if ($estado && (string)$estado['usuario'] !== (string)$estado['correcta']) {
            echo '<div class="correct-text" style="margin-top: 5px;">Respuesta correcta: ' . htmlspecialchars($estado['correcta']) . '</div>';
        }
    }
?>