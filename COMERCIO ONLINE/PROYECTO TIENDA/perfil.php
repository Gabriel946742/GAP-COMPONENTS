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
$foto_perfil = 'recursos/images/perfil.png'; // Imagen por defecto
$stmt = $db->prepare("SELECT foto, nombre, email, password FROM usuarios WHERE id = :usuario_id");
$stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario && !empty($usuario['foto']) && file_exists("recursos/images/" . $usuario['foto'])) {
    $foto_perfil = "recursos/images/" . $usuario['foto'];
}

// Verificar si el usuario es administrador
$is_admin = $_SESSION['usuario_tipo'] === 'administrador';

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

    // Verificar si se ha subido una nueva imagen
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Obtener el nombre original del archivo subido
        $imagen = $_FILES['foto']['name'];
        $imagen_path = basename($imagen); // Solo el nombre y extensión

        // Crear el directorio si no existe
        if (!file_exists(__DIR__ . '/recursos/images/')) {
            mkdir(__DIR__ . '/recursos/images/', 0777, true);
        }

        // Mover el archivo subido a la carpeta destino
        if (move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/recursos/images/' . $imagen_path)) {
            // Eliminar la imagen anterior si existe
            if (!empty($usuario['foto']) && file_exists(__DIR__ . '/recursos/images/' . $usuario['foto'])) {
                unlink(__DIR__ . '/recursos/images/' . $usuario['foto']);
            }
        } else {
            $_SESSION['mensaje_error'] = "No se pudo mover la imagen al directorio destino.";
            header('Location: perfil.php');
            exit();
        }
    } else {
        // Si no se subió una nueva imagen, mantener la imagen actual
        $imagen_path = $usuario['foto'];
    }

    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $password_hash = $usuario['password'];
    }

    // Actualizar el usuario en la base de datos
    $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, email = ?, password = ?, foto = ? WHERE id = ?");
    $stmt->execute([$nombre, $email, $password_hash, $imagen_path, $_SESSION['usuario_id']]);

    $_SESSION['usuario_nombre'] = $nombre;
    $_SESSION['usuario_email'] = $email;
    $_SESSION['mensaje'] = "Perfil actualizado correctamente ✅";

    header('Location: perfil.php');
    exit();
}

// Procesar el formulario de añadir categoría
if ($is_admin && isset($_POST['agregar_categoria'])) {
    $nombre_categoria = $_POST['nombre_categoria'];
    $descripcion_categoria = $_POST['descripcion_categoria'];
    $categoria_padre = !empty($_POST['categoria_padre']) ? $_POST['categoria_padre'] : null;

    $stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion, categoria_padre) VALUES (:nombre, :descripcion, :categoria_padre)");
    $stmt->execute(['nombre' => $nombre_categoria, 'descripcion' => $descripcion_categoria, 'categoria_padre' => $categoria_padre]);
    $_SESSION['mensaje'] = "Categoría añadida correctamente.";
}

// Procesar el formulario de eliminar categoría
if ($is_admin && isset($_POST['eliminar_categoria'])) {
    $categoria_id = $_POST['categoria_id'];

    $stmt = $db->prepare("DELETE FROM categorias WHERE id = :id");
    $stmt->execute(['id' => $categoria_id]);
    $_SESSION['mensaje'] = "Categoría eliminada correctamente.";
}

// Obtener lista de categorías para la gestión
$categorias = $db->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);

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
        
        <div class="elecciones-perfil">
            <a href="editar_perfil.php">
                <div class="caja-perfil">
                    <img src="recursos/images/editar_perfil.png" alt="Editar Perfil">
                    <h3>Editar Perfil</h3>
                </div>
            </a>

            <a href="pedidos.php">
                <div class="caja-perfil">
                    <img src="recursos/images/pedidos.png" alt="Mis Pedidos">
                    <h3>Mis Pedidos</h3>
                </div>
            </a>

            <a href="logout.php">
                <div class="caja-perfil">
                    <img src="recursos/images/cerrar-sesion.png" alt="Cerrar Sesión">
                    <h3>Cerrar Sesión</h3>
                </div>
            </a>
        </div>

        <?php if ($is_admin): ?>
            <div class="opciones-admin">
                <h3>Gestión de Productos</h3>
                <a href="agregar_producto.php"><button type="submit">Añadir Producto</button></a>
                <a href="modificar_producto.php"><button type="submit">Modificar Producto</button></a>
                <a href="eliminar_producto.php"><button type="submit">Eliminar Producto</button></a>

                <h3>Gestión de Categorías</h3>
                <a href="añadir_categoria.php"><button type="submit">Añadir Categoría</button></a>
                <a href="modificar_categoria.php"><button type="submit">Modificar Categorías</button></a>
                <a href="eliminar_categoria.php"><button type="submit">Eliminar Categoría</button></a>

                <h3>Gestión de Productos Estrella</h3>
                <a href="añadir_producto_estrella.php"><button type="submit">Añadir Estrella</button></a>
                <a href="eliminar_producto_estrella.php"><button type="submit">Eliminar Estrella</button></a>
                </form>
            </div>
        <?php endif; ?>
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







