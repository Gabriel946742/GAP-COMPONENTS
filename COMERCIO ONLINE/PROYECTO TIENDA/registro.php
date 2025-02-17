<?php
session_start();
require_once 'conexion.php';

// Procesamos el formulario de registro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmar_password = trim($_POST['confirmar_password']);

    // Validación básica
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($password) || empty($confirmar_password)) {
        $error = "Por favor, completa todos los campos obligatorios.";
    } elseif ($password !== $confirmar_password) {
        $error = "Las contraseñas no coinciden.";
    } else {
        try {
            $db = conectaDB('mi_tienda_online');

            // Verificar que el correo no esté ya registrado
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $error = "El correo ya está registrado.";
            } else {
                $nombre_archivo = 'perfil.png'; // Por defecto

                if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                    // Obtener información del archivo subido
                    $tmp_name = $_FILES['foto_perfil']['tmp_name'];
                    $original_name = basename($_FILES['foto_perfil']['name']);
                    $ext = pathinfo($original_name, PATHINFO_EXTENSION);
                    
                    // Generar un nombre único para evitar sobrescribir archivos
                    $nombre_archivo = uniqid('perfil_', true) . '.' . $ext;

                    // Directorio de destino
                    $ruta_destino = __DIR__ . '/recursos/images/' . $nombre_archivo;

                    // Crear el directorio si no existe
                    if (!file_exists(__DIR__ . '/recursos/images/')) {
                        mkdir(__DIR__ . '/recursos/images/', 0777, true);
                    }

                    // Mover el archivo subido a la carpeta de destino
                    if (!move_uploaded_file($tmp_name, $ruta_destino)) {
                        $error = "No se pudo guardar la imagen. Inténtalo de nuevo.";
                    }
                }

                if (!isset($error)) {
                    // Insertar el nuevo usuario con la foto de perfil
                    $stmt = $db->prepare("INSERT INTO usuarios (nombre, apellidos, direccion, telefono, email, password, foto) 
                    VALUES (:nombre, :apellidos, :direccion, :telefono, :email, :password, :foto)");
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt->execute([
                        'nombre' => $nombre,
                        'apellidos' => $apellidos,
                        'direccion' => $direccion,
                        'telefono' => $telefono,
                        'email' => $email,
                        'password' => $password_hash,
                        'foto' => $nombre_archivo
                    ]);

                    header('Location: login.php');
                    exit();
                }
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
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
    <title>Registro - GAP COMPONENTS</title>
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
        
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="perfil.php"><img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Abrir Perfil" class="icono-perfil img-circular"></a>
            <a href="logout.php"><img src="recursos/images/LogOut.png" alt="Cerrar Sesión" class="icono-cerrar-sesion"></a>
        <?php else: ?>
            <a href="login.php"><img src="recursos/images/perfil.png" class="icono-login"></a>
        <?php endif; ?>
            <a href="carrito.php">
                <img src="recursos/images/carrito-compras.svg" alt="Carrito" class="icono-carrito">
                <?php if (!empty($_SESSION['carrito'])): ?>
                    <span class="contador-carrito"><?= count($_SESSION['carrito']) ?></span>
                <?php endif; ?>
            </a>
        </div>
    </nav>
</header>

<main class="registro-container">
    <h2>Crea tu cuenta</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    <form action="registro.php" method="POST" enctype="multipart/form-data">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required>

        <label for="apellidos">Apellidos:</label>
        <input type="text" id="apellidos" name="apellidos" required>

        <label for="direccion">Dirección:</label>
        <input type="text" id="direccion" name="direccion" required>

        <label for="telefono">Teléfono:</label>
        <input type="text" id="telefono" name="telefono" required>

        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirmar_password">Confirmar Contraseña:</label>
        <input type="password" id="confirmar_password" name="confirmar_password" required>

        <label for="foto_perfil">Foto de Perfil:</label>
        <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*">
        <br></br>

        <button type="submit" name="registro">Registrarse</button>
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


