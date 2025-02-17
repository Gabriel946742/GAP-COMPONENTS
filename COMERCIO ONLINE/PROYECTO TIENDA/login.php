<?php
session_start();
require_once 'conexion.php';  

// Verificamos si ya está logueado
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

// Procesamos el formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $email = $_POST['usuario'];  
    $contraseña = $_POST['contraseña'];

    if (empty($email) || empty($contraseña)) {
        $error = "Por favor, ingresa ambos campos.";
    } else {
        try {
            $db = conectaDB();  
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $usuario_bd = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario_bd && password_verify($contraseña, $usuario_bd['password'])) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $usuario_bd['id'];
                $_SESSION['usuario_nombre'] = $usuario_bd['nombre'];
                $_SESSION['usuario_tipo'] = $usuario_bd['tipo'];
                $_SESSION['usuario_email'] = $usuario_bd['email'];

                // Agregar mensaje de sesión iniciada en verde
                $_SESSION['mensaje'] = "Sesión iniciada correctamente ✅";

                header('Location: index.php');
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $error = "Ocurrió un error al intentar iniciar sesión. Inténtalo de nuevo más tarde.";
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
    <title>Iniciar sesión - GAP COMPONENTS</title>
    <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="recursos/css/style.css">
</head>
<body>

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
            <a href="login.php"><img src="recursos/images/perfil.png" class="icono-login"></a>
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
    <h2 class="iniciar-sesion-title">Iniciar sesión</h2>
    <div class="login">
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="usuario">Correo Electrónico:</label>
                <input type="text" name="usuario" id="usuario" required>
            </div>
            <div class="form-group">
                <label for="contraseña">Contraseña:</label>
                <input type="password" name="contraseña" id="contraseña" required>
            </div>
            <button type="submit">Iniciar sesión</button>
        </form>
        <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
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

<!-- JavaScript para mostrar el mensaje con fade -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const mensaje = document.querySelector('.mensaje-flotante');
        if (mensaje) {
            mensaje.classList.add('visible');
            setTimeout(() => {
                mensaje.classList.remove('visible');
            }, 3000);
        }
    });
</script>

<script>
    const COOKIE_TIMEOUT = 24 * 60 * 60 * 1000;  // 1 día en milisegundos

    // Función para comprobar si ya se ha aceptado las cookies
    function hasAcceptedCookies() {
        return localStorage.getItem('cookieAccepted') === 'true';
    }

    // Muestra el banner solo si no se ha aceptado las cookies
    function showCookieBanner() {
        if (!hasAcceptedCookies()) {
            const banner = document.getElementById('cookie-banner');
            banner.style.display = 'block';
        }
    }

    // Guarda la aceptación de cookies en localStorage
    function acceptCookies() {
        localStorage.setItem('cookieAccepted', 'true');
        const banner = document.getElementById('cookie-banner');
        banner.style.display = 'none'; 
    }

    document.getElementById('accept-cookies').addEventListener('click', acceptCookies);

    window.onload = showCookieBanner;

</script>

</body>
</html>
