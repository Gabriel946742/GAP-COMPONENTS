<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    header('Location: login.php');
    exit();
}

$db = conectaDB();

// Obtener la foto de perfil del usuario logueado
$foto_perfil = 'recursos/images/perfil.png'; // Imagen por defecto
$stmt = $db->prepare("SELECT foto FROM usuarios WHERE id = :usuario_id");
$stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario && !empty($usuario['foto']) && file_exists("recursos/images/" . $usuario['foto'])) {
    $foto_perfil = 'recursos/images/' . $usuario['foto'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $producto_id = $_POST['producto_id'];
    $fecha_destacado = date('Y-m-d H:i:s');

    try {
        $stmt = $db->prepare("INSERT INTO productos_estrella (producto_id, fecha_destacado) VALUES (?, ?)");
        $stmt->execute([$producto_id, $fecha_destacado]);

        $_SESSION['mensaje'] = "Producto destacado correctamente ✅";
        header('Location: perfil.php');
        exit();
    } catch (PDOException $e) {
        $error = "Error al destacar el producto: " . $e->getMessage();
    }
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
        <h3>Gestionar productos estrella</h3>
        <form action="añadir_producto_estrella.php" method="POST">
            <label for="producto">Seleccionar producto para destacar:</label>
            <select name="producto_id" id="producto">
                <?php
                // Obtener todos los productos disponibles
                $stmt = $db->query("SELECT id, nombre FROM productos");
                $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($productos as $producto) {
                    echo "<option value='{$producto['id']}'>{$producto['nombre']}</option>";
                }
                ?>
            </select>
            <button type="submit" name="destacar_producto">Destacar producto</button>
        </form>
    </main>
</div>

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