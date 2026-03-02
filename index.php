<?php
    include 'funciones.php';
    include 'fun-consultas.php';
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Prototipo</title>
    </head>
    <body>
        <form action="test.php" method="POST">
            
            <select id="select_modulo" name="modulo" required>
                <option value="">Selecciona un modulo</option>
                <?php
                    try {
                        desplegableModulos('DAM'); 
                    }
                    catch(PDOException $e)  {
                        error($e);
                    }
                ?>
            </select>
            
            <select id="select_test" name="test" disabled required>
                <option value="">Primero selecciona un modulo</option>
            </select>
            
            <input type="submit" value="Realizar Test" name="iniciar_test">
        </form>

        <script>
            const selectModulo = document.getElementById('select_modulo');
            const selectTest = document.getElementById('select_test');

            selectModulo.addEventListener('change', function() {
                const moduloSeleccionado = this.value;

                if (moduloSeleccionado === "") {
                    selectTest.innerHTML = '<option value="">Selecciona un modulo</option>';
                    selectTest.disabled = true;
                    return;
                }

                fetch(`obtener_tests.php?ciclo=DAM&modulo=${encodeURIComponent(moduloSeleccionado)}`)
                    .then(respuesta => respuesta.json())
                    .then(datos => {
                        selectTest.innerHTML = ''; 
                        
                        if (!datos.error) {
                            if (datos === 'vacio') {
                                selectTest.innerHTML = '<option value="">No hay tests disponibles para este modulo</option>';
                                selectTest.disabled = true; // CORRECCIÓN: Aseguramos que se vuelva a bloquear
                            } else {
                                selectTest.innerHTML = '<option value="">Selecciona un test</option>';
                                
                                datos.forEach(test => {
                                    const opcion = document.createElement('option');
                                    opcion.value = test.id_test;
                                    opcion.textContent = test.nombre;
                                    selectTest.appendChild(opcion);
                                });
                                
                                selectTest.disabled = false; 
                            }
                        } else {
                            console.error("Error desde PHP:", datos.error);
                        }
                    })
                    .catch(error => {
                        console.error("Hubo un error con la petición Fetch:", error);
                    });
            });
        </script>
    </body>
</html>