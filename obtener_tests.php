<?php
    // obtener_tests.php
    include 'funciones/funciones.php';

    header('Content-Type: application/json; charset=utf-8');

    // Recupera los datos que le enviamos desde el fetch (js)
    $ciclo = $_GET['ciclo'];
    $modulo = $_GET['modulo'];

    try {
        $conn = conexionBD();
        $stmt = $conn->prepare("SELECT id_test, nombre FROM tests WHERE ciclo = :ciclo AND modulo = :modulo");
        $stmt->bindParam(':ciclo', $ciclo);
        $stmt->bindParam(':modulo', $modulo);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si la cosulta no devulve nada envia 'vacio' al JS, pero si devuelve algun test, los envia todos al JS
        if (empty($resultados)) {
            echo json_encode('vacio');
        } else {
            echo json_encode($resultados);
        }

    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);

    } finally {
        $conn = null;
    }
    
?>