<?php
    session_start();
    // Temporal ----------------------
        $_SESSION['Nombre'] = 'Pepe';
        $_SESSION['Apellidos'] = 'Malho';
        $_SESSION['Ciclo'] = 'DAM';
    // -------------------------------
    include 'funciones/funciones.php';
    include 'funciones/fun-index.php';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>TestsApp</title>
        <link rel="stylesheet" href="css/style.css">  <!-- Hoja CSS -->
    </head>
    <body>
        <div class="container">
            <h1>TestsApp</h1>
            <p class="subtitle">Selecciona tu módulo y evalúa tus conocimientos.</p>

            <form action="test.php" method="POST">
                <div class="form-group">
                    <!-- Esto es solo para indicar al JS el ciclo -->
                    <input id="ciclo" name="ciclo" value="<?php echo $_SESSION['Ciclo']; ?>" hidden></input> 
                    
                    <select id="select_modulo" name="modulo" required>
                        <option value="">Selecciona un módulo</option>
                        <?php
                            try {  // Rellena con los modulos del ciclo
                                desplegableModulos($_SESSION['Ciclo']); 
                            }
                            catch(PDOException $e)  {
                                $error = error($e);
                                echo $error;
                            }
                        ?>
                    </select>
                    
                    <!-- Estado base, cambia de forma dinamica con el JS -->
                    <select id="select_test" name="test" disabled required>
                        <option value="">Primero selecciona un módulo</option>
                    </select>
                </div>
                
                <input type="submit" value="Comenzar Test" name="iniciar_test">
            </form>
        </div>
    </body>
    <script src="js/script.js"></script>  <!-- Hoja JS -->
</html>