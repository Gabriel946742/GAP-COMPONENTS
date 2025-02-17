<?php
require 'conexion.php';
$db = conectaDB('mi_tienda_online');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = (int)$_POST['producto_id'];
    $pedido_id = (int)$_POST['pedido_id'];
    $usuario_id = (int)$_POST['usuario_id'];
    $valor = (int)$_POST['valor'];

    // Verificar si ya se ha valorado este producto en este pedido por el usuario
    $stmt = $db->prepare("SELECT COUNT(*) FROM valoraciones WHERE producto_id = :producto_id AND usuario_id = :usuario_id");
    $stmt->execute(['producto_id' => $producto_id, 'usuario_id' => $usuario_id]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: detalles_pedido.php?pedido_id=$pedido_id&error=Ya has valorado este producto.");
        exit();
    }

    // Insertar la valoración
    $stmt = $db->prepare("INSERT INTO valoraciones (producto_id, usuario_id, valor, fecha_valoracion) VALUES (:producto_id, :usuario_id, :valor, NOW())");
    $stmt->execute([
        'producto_id' => $producto_id,
        'usuario_id' => $usuario_id,
        'valor' => $valor
    ]);

    // Redirigir con mensaje de éxito
    header("Location: detalles_pedido.php?pedido_id=$pedido_id&success=Valoración registrada.");
    exit();
}
?>


