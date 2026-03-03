<?php
    session_start();
    include 'funciones/funciones.php';
    include 'funciones/fun-test.php';

    if (isset($_POST['iniciar_test'])) {  
        // Como esta pestaña recibe datos y luego se llama a si misma, si recibe datos los guarda en cookies para que no pete
        $_SESSION['modulo'] = test_input($_POST['modulo']);
        $_SESSION['test'] = test_input($_POST['test']);
    }

    if (!isset($_SESSION['modulo']) || !isset($_SESSION['test'])) {  
        // Si estos datos no existen detecta que no se ha seleccionado ningun test
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
                <h2>Error: No has seleccionado ningún test.</h2>
                <a href='index.php' style='color:#4F46E5;'>Volver al inicio</a>
             </div>");
    }

    $modulo = $_SESSION['modulo'];
    $id_test = $_SESSION['test'];
    
    $preguntas = preguntasTest($modulo, $id_test);  // Recupera las preguntas de la BD
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizando Test - <?php echo htmlspecialchars($modulo); ?></title>  <!-- Pone el nombre del tets -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviar_test'])) {  
                // Una vez terminado el test y enviado lo comprueba y muestra el resultado
                $rUsuario = isset($_POST['respuestas']) ? $_POST['respuestas'] : []; 
                $resultado = comprobarRespuestas($rUsuario, $preguntas);
                
                echo "<div class='results'>";
                echo "<h2>¡Test Completado!</h2>";
                echo "<p class='subtitle'>Resultados del módulo: <b>" . htmlspecialchars($modulo) . "</b></p>";
                
                echo "<p>Has acertado <b>" . $resultado['aciertos'] . "</b> de " . $resultado['total'] . " preguntas.</p>";
                echo "<div class='nota-final'>" . $resultado['nota_final']/10 . "/10</div>";
                
                echo "<a href='index.php' class='btn btn-secondary'>Volver al Inicio</a>";
                echo "</div>";

            } else {  // Al iniciar la pagina detecta un envio, como no es 'enviar_test' imprime el test
                echo "<h2>Test de " . htmlspecialchars($modulo) . "</h2>";
                echo "<p class='subtitle'>Lee atentamente y selecciona la respuesta correcta.</p>";
                mostrarTest($preguntas);
            }
        ?>
    </div>
</body>
</html>