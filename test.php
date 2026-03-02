<?php
    // session_start() siempre debe ir lo más arriba posible
    session_start();
    include 'funciones.php';
    include 'fun-consultas.php';

    // 1. GESTIÓN DE SESIONES
    // Si venimos de index.php (el form tiene el name "iniciar_test"), guardamos los datos en la sesión
    if (isset($_POST['iniciar_test'])) {
        $_SESSION['modulo'] = test_input($_POST['modulo']);
        $_SESSION['test'] = test_input($_POST['test']);
    }

    // Verificamos que no nos hayamos metido a la URL directamente sin pasar por el index
    if (!isset($_SESSION['modulo']) || !isset($_SESSION['test'])) {
        die("Error: No has seleccionado ningún test. <a href='index.php'>Volver al inicio</a>");
    }

    $modulo = $_SESSION['modulo'];
    $id_test = $_SESSION['test'];
    
    // Obtenemos las preguntas de la BD
    $preguntas = preguntasTest($modulo, $id_test);

    // 2. LÓGICA DE LA PÁGINA (Mostrar Test vs Mostrar Resultados)
    // Si el usuario acaba de pulsar "Enviar Test" en el formulario de las preguntas
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviar_test'])) {
        
        // Si dejó alguna pregunta en blanco, evitamos el error
        $respuestasUsuario = isset($_POST['respuestas']) ? $_POST['respuestas'] : []; 
    
        // Calculamos los aciertos
        $resultado = comprobarRespuestas($respuestasUsuario, $preguntas);
        
        echo "<div style='text-align: center; margin-top: 50px; font-family: Arial;'>";
        echo "<h2>¡Test terminado!</h2>";
        echo "<h3>Resultados de: " . htmlspecialchars($modulo) . "</h3>";
        echo "<p>Has acertado <strong>" . $resultado['aciertos'] . "</strong> de " . $resultado['total'] . " preguntas.</p>";
        echo "<p>Tu nota final es: <strong>" . $resultado['nota_final'] . " sobre 100</strong>.</p>";
        
        echo "<br><a href='index.php'><button style='padding: 10px; cursor:pointer;'>Volver al Inicio</button></a>";
        echo "</div>";

        // (Opcional) Borrar la sesión para obligar a seleccionar desde index la próxima vez
        // session_unset(); 

    } else {
        // Si NO ha enviado el test todavía, se lo mostramos para que lo haga
        echo "<h2 style='font-family: Arial;'>Realizando test de " . htmlspecialchars($modulo) . "</h2>";
        mostrarTest($preguntas);
    }
?>