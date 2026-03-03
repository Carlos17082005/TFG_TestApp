<?php
    // obtener_tests.php
    include 'funciones/funciones.php';

    header('Content-Type: application/json; charset=utf-8');

    $ciclo = $_GET['ciclo'];
    $modulo = $_GET['modulo'];

    try {
        $conn = conexionBD();
        $stmt = $conn->prepare("SELECT id_test, nombre FROM tests WHERE ciclo = :ciclo AND modulo = :modulo");
        $stmt->bindParam(':ciclo', $ciclo);
        $stmt->bindParam(':modulo', $modulo);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // --- NUEVA LÓGICA ---
        // Comprobamos si la base de datos no devolvió ninguna fila
        if (empty($resultados)) {
            echo json_encode('vacio'); // Enviamos exactamente la cadena 'vacio'
        } else {
            echo json_encode($resultados); // Enviamos los tests encontrados
        }

    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    } finally {
        $conn = null;
    }
    
?>