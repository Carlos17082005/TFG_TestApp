<?php
    function test_input($data) {  // Funcion para limpiar campos y evitar inyeccion de codigo
    // Recibe una cadena o numero
    // Devuelve la misma cadena o numero 
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    function conexionBD()  {  // Funcion para conectarse a la base de datos
    // Devuelve una variable con la conexion activa
        try  {
            $servername = "localhost";
            $username = "root";
            $password = "rootroot";
            $dbname = "testapp";

            $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;

        }  catch (PDOException $e)  {
            throw $e;
        }
    }

    function error($e)  {  // Funcion para generar un mensaje de error personalizado
    // Recibe un error
    // Imprime en pantalla un mesaje de error segun el codigo del error
        $error = $e -> errorInfo;
        $codigo_error = $error[1];

        switch ($codigo_error)  {
            case 1062:
                $text = 'Error: Primary key duplicada';
                break;
            case 1452:
                $text = 'Error: Foreing key no encontrada';
                break;
            case 1064:
                $text = 'Error en la sintaxis SQL';
                break;
            // 1054  Campo desconocido
            // 1054  Unknown column
            // 1048 Column 'id_reserva' cannot be null
            default:
                $text = '';
        }
        
        if ($text == "")  {
            echo '<p style="text-align: center; color: red;">' . $e->getMessage() . '</p>';
        }  else  {
            echo '<p style="text-align: center; color: red;">' . $text . '</p>';
        }
    }
?>