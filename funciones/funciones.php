<?php
    // =========================================================================
    // INTERRUPTOR GLOBAL: Cambia a 'false' para desactivar TODA la aleatoriedad
    // =========================================================================
    define('ACTIVAR_ALEATORIEDAD', true); 

    function test_input($data) {  
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    function conexionBD()  {  
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

    function error($e)  {  
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

    function normalizarRespuesta($texto) {
        $texto = str_replace(['’', '‘', '´', '`'], "'", $texto);
        $texto = mb_strtolower($texto, 'UTF-8');
        $reemplazos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u'
        ];
        $texto = strtr($texto, $reemplazos);
        $texto = str_replace(['¿', '¡'], '', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    }
?>