<?php
    function decode_tf($id_pregunta, $contenido)  {
        // Reecibe un array asositivo y el id de  la pregunta para poder asosiar la reespuesta a la pregunta
        // El value tiene que se exactamente igual que en la respuesta de la BD (influyen mayusculas)   ---> si usamos la de normalizar no deberia importar
        echo '<label class="option-label"><input type="radio" name="respuestas[' . $id_pregunta . ']" value="True" required> True</label>';
        echo '<label class="option-label"><input type="radio" name="respuestas[' . $id_pregunta . ']" value="False" required> False</label>';
    }

    function decode_conecta($id_pregunta, $contenido)  {
        //Reecibe un array asositivo y el id de  la pregunta para poder asosiar la reespuesta a la pregunta
        $claves = array_keys($contenido['div-2']);

        echo '<div>';
        echo '<div id="div-1">';
        foreach ($contenido['div-1'] as $number => $texto_opcion)  {
            echo $number . '. ' . $texto_opcion;
            echo '<select name="respuestas[' . $id_pregunta . '][' . $number . ']" required>';
            echo '<option value="">--</option>';
            foreach ($claves as $clave)  {
                echo '<option value="' . $clave . '">' . $clave . '</option>';
            }
            echo '</select>';
        }
        echo '</div>';

        echo '<div id="div-2">';
        foreach ($contenido['div-2'] as $letra => $texto_opcion)  {
            echo $letra . '. ' . $texto_opcion . '<br>';
        }
        echo '</div>';

        echo '</div>';
    }

    function decode_texto($id_pregunta, $contenido)  {  // hay que poner que al crear la pregunte si la cadena 2 esta vacia ponga automaticamente un '.'
        // Reecibe un array asositivo y el id de  la pregunta para poder asosiar la reespuesta a la pregunta
        $pregunta = $contenido['pregunta'];
        echo '<h3>' . $pregunta['cadena1'] . ' __________ ' . $pregunta['cadena2'] . '</h3>';
        echo 'Escribe tu respuesta: <br>';
        echo '<label class="option-label"><input type="text" name="respuestas[' . $id_pregunta . ']" required autocomplete="off"></label>';
    }

    function decode_number($id_pregunta, $contenido)  {
        // Reecibe un array asositivo y el id de  la pregunta para poder asosiar la reespuesta a la pregunta
        echo 'Escribe tu respuesta: <br>';
        echo '<label class="option-label"><input type="number" name="respuestas[' . $id_pregunta . ']" required step="0.01"></label>';
    }

?>