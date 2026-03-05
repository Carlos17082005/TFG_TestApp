<?php
    session_start();
    include 'funciones/funciones.php';
    include 'funciones/fun-test.php';

    if (isset($_POST['iniciar_test'])) {  
        $_SESSION['modulo'] = test_input($_POST['modulo']);
        $_SESSION['test'] = test_input($_POST['test']);
        
        unset($_SESSION['preguntas_mezcladas']);
        unset($_SESSION['orden_internos']);
    }

    if (!isset($_SESSION['modulo']) || !isset($_SESSION['test'])) {  
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
                <h2>Error: No has seleccionado ningún test.</h2>
                <a href='index.php' style='color:#4F46E5;'>Volver al inicio</a>
             </div>");
    }

    $modulo = $_SESSION['modulo'];
    $id_test = $_SESSION['test'];
    
    $preguntas_bd = preguntasTest($modulo, $id_test);  

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviar_test'])) {
        $preguntas = isset($_SESSION['preguntas_mezcladas']) ? $_SESSION['preguntas_mezcladas'] : $preguntas_bd;
    } else {
        $preguntas = $preguntas_bd;
        
        // --- INTERRUPTOR APLICADO AQUÍ ---
        if (ACTIVAR_ALEATORIEDAD) {
            shuffle($preguntas); 
        }
        
        $_SESSION['preguntas_mezcladas'] = $preguntas;
        $_SESSION['orden_internos'] = [];
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizando Test - <?php echo htmlspecialchars($modulo); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviar_test'])) {  
                $rUsuario = isset($_POST['respuestas']) ? $_POST['respuestas'] : []; 
                $resultado = comprobarRespuestas($rUsuario, $preguntas);
                
                echo "<div class='results'>";
                echo "<h2>¡Test Completado!</h2>";
                echo "<p class='subtitle'>Resultados del módulo: <b>" . htmlspecialchars($modulo) . "</b></p>";
                
                echo "<p>Has acertado <b>" . round($resultado['aciertos'], 2) . "</b> de " . $resultado['total'] . " preguntas.</p>";
                echo "<div class='nota-final'>" . $resultado['nota_final']/10 . "/10</div>";
                
                echo "<a href='index.php' class='btn btn-secondary' style='margin-bottom: 2rem;'>Volver al Inicio</a>";
                echo "</div>";

                mostrarTest($preguntas, $resultado['informe']);

            } else {  
                echo "<h2>Test de " . htmlspecialchars($modulo) . "</h2>";
                echo "<p class='subtitle'>Lee atentamente y selecciona la respuesta correcta.</p>";
                mostrarTest($preguntas);
            }
        ?>
    </div>
</body>
</html>