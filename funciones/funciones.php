<?php
    function test_input($data) {  // Permite realizar una limpieza de los campos de un formulario para evitar inyeccion de codigo
    // Recibe un dato strig o number
    // Devulve el mismo dato pero sin caracteres especiales o codigo que pueda afectar nuestro codigo
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    function conexionBD()  {  // Permite conectarse a la base de datos testapp
    // Devuelve una variable con la informacion de la conexion, para cerrar la conexion $conn = null
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

    function error($e)  {  // Funcion para imprimir errores SQL personalisados
    // Recibe una variable con la informacion del error
    // Devuelve el mensaje personalisado segun el codigo del error
        $error = $e -> errorInfo;
        $codigo_error = $error[1];

        switch ($codigo_error)  {
            case 1062:
                $text = 'Error: Primary key duplicada'; break;
            case 1452:
                $text = 'Error: Foreing key no encontrada'; break;
            case 1064:
                $text = 'Error en la sintaxis SQL'; break;
            default:
                $text = '';
        }
        
        if ($text == "")  {
            return '<div class="error-msg">' . $e->getMessage() . '</div>';
        }  else  {
            return '<div class="error-msg">' . $text . '</div>';
        }
    }
?>