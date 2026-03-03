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
            if ($conn !== null) { $conn = null; }
        }
    }
?>