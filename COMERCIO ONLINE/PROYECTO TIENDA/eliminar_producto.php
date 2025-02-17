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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $producto_id = $_POST['producto_id'];

    if (empty($producto_id)) {
        $error = "Por favor, selecciona un producto para eliminar.";
    } else {
        try {
            // Eliminar el producto de la base de datos
            $db = conectaDB();
            $stmt = $db->prepare("DELETE FROM productos WHERE id = :id");
            $stmt->execute(['id' => $producto_id]);

            $_SESSION['mensaje'] = "Producto eliminado correctamente ✅";
            header('Location: perfil.php');
            exit();
        } catch (PDOException $e) {
            $error = "Ocurrió un error al eliminar el producto.";
        }
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
    <h2>Eliminar Producto</h2>
    <div class="perfil">
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form action="eliminar_producto.php" method="POST">
            <div class="form-group">
                <label for="producto_id">Selecciona el Producto:</label>
                <br></br>
                <select name="producto_id" id="producto_id" required>
                    <?php foreach ($productos as $producto): ?>
                        <option value="<?= $producto['id'] ?>"><?= $producto['nombre'] ?> - <?= $producto['precio'] ?> €</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Eliminar Producto</button>
        </form>
    </div>
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


