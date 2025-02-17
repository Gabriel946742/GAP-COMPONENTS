<?php
session_start();
require 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$db = conectaDB('mi_tienda_online');

// Obtener la foto de perfil del usuario logueado
$foto_perfil = 'recursos/images/perfil.png'; 
$stmt = $db->prepare("SELECT foto, nombre, email, password FROM usuarios WHERE id = :usuario_id");
$stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Si la foto existe en la base de datos y no está vacía
if ($usuario && !empty($usuario['foto']) && file_exists("recursos/images/" . $usuario['foto'])) {
    $foto_perfil = "recursos/images/" . $usuario['foto'];
}

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_perfil'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verificar si el correo electrónico ya está en uso
    if ($email !== $usuario['email']) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $email_existente = $stmt->fetchColumn();

        if ($email_existente > 0) {
            $_SESSION['mensaje_error'] = "El correo electrónico ya está en uso por otro usuario";
            header('Location: perfil.php');
            exit();
        }
    }

    // Manejar la subida de una nueva foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $imagen = $_FILES['foto'];
        $ext = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));

        // Validar extensión del archivo
        $extensiones_validas = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $extensiones_validas)) {
            $_SESSION['mensaje_error'] = "Formato de imagen no válido. Solo se permiten JPG, PNG o GIF.";
            header('Location: perfil.php');
            exit();
        }

        // Generar un nombre único para la imagen
        $imagen_path = uniqid('perfil_', true) . '.' . $ext;
        $imagen_destino = __DIR__ . '/recursos/images/' . $imagen_path;

        // Mover el archivo al directorio de destino
        if (!move_uploaded_file($imagen['tmp_name'], $imagen_destino)) {
            $_SESSION['mensaje_error'] = "Error al guardar la imagen. Inténtalo de nuevo.";
            header('Location: perfil.php');
            exit();
        }

        // Eliminar la imagen anterior si existe
        if (!empty($usuario['foto']) && file_exists(__DIR__ . '/recursos/images/' . $usuario['foto'])) {
            unlink(__DIR__ . '/recursos/images/' . $usuario['foto']);
        }
    } else {
        // Si no se subió una nueva imagen, conservar la actual
        $imagen_path = $usuario['foto'];
    }

    // Manejo del password
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $password_hash = $usuario['password'];
    }

    // Actualizar datos en la base de datos
    $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, email = ?, password = ?, foto = ? WHERE id = ?");
    $stmt->execute([$nombre, $email, $password_hash, $imagen_path, $_SESSION['usuario_id']]);

    $_SESSION['usuario_nombre'] = $nombre;
    $_SESSION['usuario_email'] = $email;
    $_SESSION['mensaje'] = "Perfil actualizado correctamente ✅";

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
    <title>Perfil - GAP COMPONENTS</title>
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
                <a href="perfil.php"><img src="<?= htmlspecialchars($foto_perfil); ?>" alt="Abrir Perfil" class="icono-perfil img-circular"></a>
                <a href="logout.php"><img src="recursos/images/LogOut.png" alt="Cerrar Sesión" class="icono-cerrar-sesion"></a>
                <a href="carrito.php">
                    <img src="recursos/images/carrito-compras.svg" alt="Carrito" class="icono-carrito">
                    <?php if (!empty($_SESSION['carrito'])): ?>
                        <?php 
                            $total_productos = 0;
                            foreach ($_SESSION['carrito'] as $item) {
                                $total_productos += $item['cantidad'];
                            }
                        ?>
                        <span class="contador-carrito"><?= $total_productos ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </nav>
    </header>

    <main>
        <div class="perfil-card">
            <div class="perfil-imagen">
                <img src="<?= htmlspecialchars($foto_perfil); ?>" alt="Imagen de Perfil" class="img-circular">
            </div>
            <div class="perfil-info">
                <h2><?= htmlspecialchars($_SESSION['usuario_nombre']); ?></h2>
                <p>Email: <?= htmlspecialchars($_SESSION['usuario_email']); ?></p>
            </div>
        </div>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="mensaje-error">
                <p><?= $_SESSION['mensaje_error']; ?></p>
            </div>
            <?php unset($_SESSION['mensaje_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje-success">
                <p><?= $_SESSION['mensaje']; ?></p>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <h3>Editar Perfil</h3>
        <form action="perfil.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="editar_perfil" value="1">

            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($usuario['nombre']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($usuario['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password">
                <small><p>Deja este campo vacío si no deseas cambiar la contraseña</p></small>
            </div>

            <div class="form-group">
                <label for="foto">Foto de perfil:</label>
                <input type="file" name="foto" id="foto">
                <p>Imagen actual: <?= $usuario['foto'] ?></p>
            </div>

            <button type="submit">Actualizar Perfil</button>
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