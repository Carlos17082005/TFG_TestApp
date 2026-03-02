<?php
    function desplegableModulos($ciclo) {
        try  {
            $conn = conexionBD();
            $stmt = $conn->prepare("SELECT modulo FROM modulos WHERE ciclo = (:ciclo)");
            $stmt->bindParam(':ciclo', $ciclo);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $resultado=$stmt->fetchAll();
            foreach($resultado as $row) {
                echo '<option value="'.$row['modulo'].'">'.$row['modulo'].'</option>';
            }

        }  catch  (PDOException $e)  {
            throw $e;

        }  finally  {
            if ($conn !== null) {
                $conn = null;
            }
        }
    }

    function preguntasTest($modulo, $id_test)  {
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
            if ($conn !== null) {
                $conn = null;
            }
        }
    }

    function mostrarTest($preguntas) {
        // Iniciamos el formulario (suponiendo que envías a procesar_test.php o a la misma página)
        echo '<form name="respuestas" method="POST" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
        
        foreach ($preguntas as $row) {
            $id_pregunta = $row['id_pregunta'];
            
            // Convertimos el string JSON de la base de datos a un array de PHP
            // El "true" final es vital para que devuelva un array y no un objeto
            $contenido = json_decode($row['contenido'], true); 
            
            echo '<div style="margin-bottom: 20px; padding: 10px; border: 1px solid #ccc;">';
            // Imprimimos el título de la pregunta
            echo '<h3>' . htmlspecialchars($contenido['pregunta']) . '</h3>';

            // Recorremos las opciones (a, b, c...)
            foreach ($contenido['opciones'] as $letra => $texto_opcion) {
                echo '<label style="display: block; margin-bottom: 5px;">';
                // Creamos el radio button. El "value" es la letra (a, b o c)
                echo '<input type="radio" name="respuestas[' . $id_pregunta . ']" value="' . $letra . '" required> ';
                echo htmlspecialchars($letra . ') ' . $texto_opcion);
                echo '</label>';
            }
            
            echo '</div>';
        }
        
        echo '<button type="submit" name="enviar_test">Enviar Test</button>';
        echo '</form>';
    }

    function comprobarRespuestas($respuestasUsuario, $preguntas) {
        $aciertos = 0;
        $totalPreguntas = count($preguntas);
        
        // Aquí guardaremos un informe detallado por si quieres mostrar qué falló
        $informe = [];

        foreach ($preguntas as $row) {
            $id_pregunta = $row['id_pregunta'];
            $contenido = json_decode($row['contenido'], true);
            
            $respuesta_correcta = $contenido['respuesta']; // Ejemplo: "a"
            
            // Comprobamos si el usuario respondió a esta pregunta en concreto
            $respuesta_usuario = isset($respuestasUsuario[$id_pregunta]) ? $respuestasUsuario[$id_pregunta] : null;

            // ¿Acertó?
            $es_correcta = ($respuesta_usuario === $respuesta_correcta);

            if ($es_correcta) {
                $aciertos++;
            }

            // Guardamos los datos en el informe
            $informe[$id_pregunta] = [
                'pregunta' => $contenido['pregunta'],
                'tu_respuesta' => $respuesta_usuario,
                'respuesta_correcta' => $respuesta_correcta,
                'acierto' => $es_correcta
            ];
        }

        // Calculamos la nota sobre 100 (para que encaje con tu tabla `puntuacion`)
        $nota_final = ($totalPreguntas > 0) ? round(($aciertos / $totalPreguntas) * 100) : 0;

        // Devolvemos un array con todos los datos procesados
        return [
            'aciertos' => $aciertos,
            'total' => $totalPreguntas,
            'nota_final' => $nota_final,
            'detalle' => $informe
        ];
    }
?>