<?php
session_start();
require 'conexion.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    header('Location: login.php');
    exit();
}

$db = conectaDB();

// Obtener la foto de perfil del usuario logueado
$foto_perfil = 'recursos/images/perfil.png'; 
$stmt = $db->prepare("SELECT foto FROM usuarios WHERE id = :usuario_id");
$stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Si la foto existe en la base de datos y no está vacía
if ($usuario && !empty($usuario['foto']) && file_exists("recursos/images/" . $usuario['foto'])) {
    $foto_perfil = 'recursos/images/' . $usuario['foto'];
}

// Obtener productos para el formulario
$stmt = $db->query("SELECT * FROM productos");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar_producto'])) {
    $producto_id = $_POST['producto_eliminar'];

    // Eliminar el producto de la tabla productos_estrella
    $stmt = $db->prepare("DELETE FROM productos_estrella WHERE id = ?");
    $stmt->execute([$producto_id]);

    $_SESSION['mensaje'] = "Producto eliminado de los destacados ✅";
    header('Location: perfil.php');
    exit();
}



?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Añadir Producto - GAP COMPONENTS</title>
    <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="recursos/css/style.css">
</head>
<body>
<div class="main-container">
    <header>
        <div class="header-title">
        <h1><a href="index.php"><img src="recursos/images/Logo.png" alt="Logo" class="Logo-header"></a></h1>
        </div>
        <nav>
            <a href="index.php">INICIO</a>
            <a href="productos.php">PRODUCTOS</a>
            <div class="buscador-pos">
            <form action="buscar.php" method="GET" class="form-buscador">
                <input 
                    type="text" 
                    name="query" 
                    placeholder="Buscar productos" 
                    required 
                    class="input-buscador"
                >
                <button type="submit" class="btn-buscador"><img src="recursos/images/lupa.png" alt="Buscar" class="icono-buscar"></button>
            </form>
            </div>
            <div class="carrito-icono">
                <a href="perfil.php"><img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Abrir Perfil" class="icono-perfil img-circular"></a>
                <a href="logout.php"><img src="recursos/images/LogOut.png" alt="Cerrar Sesión" class="icono-cerrar-sesion"></a>
            </div>
        </nav>
    </header>

<main>
    <h3>Eliminar producto Estrella</h3>
    <form action="eliminar_producto_estrella.php" method="POST">
        <label for="producto_eliminar">Seleccionar producto para eliminar:</label>
        <select name="producto_eliminar" id="producto_eliminar">
            <?php
            $stmt = $db->prepare("SELECT pe.id, p.nombre FROM productos_estrella pe
                                JOIN productos p ON pe.producto_id = p.id");
            $stmt->execute();
            $productos_destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($productos_destacados as $producto) {
                echo "<option value='{$producto['id']}'>{$producto['nombre']}</option>";
            }
            ?>
        </select>
        <button type="submit" name="eliminar_producto">Eliminar producto destacado</button>
    </form>
</main>

<footer>
    <div class="footer-container">
        <div class="footer-column">
            <h3>Sobre Nosotros</h3>
            <ul>
                <li><a href="#">Quiénes somos</a></li>
                <li><a href="#">Historia</a></li>
                <li><a href="#">Nuestro equipo</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>Empresa</h3>
            <ul>
                <li><a href="#">Política de devoluciones</a></li>
                <li><a href="#">Política de privacidad</a></li>
                <li><a href="#">Política de cookies</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>Redes Sociales</h3>
            <ul class="social-links">
                <li><a href="https://www.instagram.com" target="_blank"><img src="recursos/images/logo_instagram.png" alt="Instagram"> Instagram</a></li>
                <li><a href="https://www.twitter.com" target="_blank"><img src="recursos/images/logo_x.png" alt="Twitter"> Twitter</a></li>
                <li><a href="https://www.facebook.com" target="_blank"><img src="recursos/images/logo_facebook.png" alt="Facebook"> Facebook</a></li>
            </ul>
        </div>
        <div class="footer-logo">
            <h1><a href="index.php"><img src="recursos/images/Logo.png" alt="Logo" class="Logo-header"></a></h1>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2024 Diseñado por Gabriel Aracil</p>
    </div>
</footer>
</body>
</html>