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

if ($usuario && !empty($usuario['foto']) && file_exists("recursos/images/" . $usuario['foto'])) {
    $foto_perfil = 'recursos/images/' . $usuario['foto'];
}

// Obtener la lista de categorías
try {
    $stmt = $db->query("SELECT id, nombre FROM categorias");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener las categorías: " . $e->getMessage();
}

// Obtener datos de la categoría seleccionada
if (isset($_GET['id'])) {
    $categoria_id = $_GET['id'];

    try {
        $stmt = $db->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->execute([$categoria_id]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$categoria) {
            $error = "Categoría no encontrada.";
        }
    } catch (PDOException $e) {
        $error = "Error al obtener los datos de la categoría: " . $e->getMessage();
    }
}

// Procesar el formulario de modificación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $categoria_padre = empty($_POST['categoria_padre']) ? NULL : $_POST['categoria_padre'];
    $imagen_actual = $_POST['imagen_actual'];
    $nueva_imagen = $_FILES['imagen']['name'];

    // Si no se seleccionó una nueva imagen, mantener la imagen actual
    $imagen = $nueva_imagen ? basename($nueva_imagen) : $imagen_actual;

    if ($nueva_imagen) {
        $ruta_imagen = "recursos/images/" . $imagen;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_imagen);
    }

    try {
        $stmt = $db->prepare("UPDATE categorias SET nombre = ?, descripcion = ?, categoria_padre = ?, imagen = ? WHERE id = ?");
        $stmt->execute([$nombre, $descripcion, $categoria_padre, $imagen, $_POST['categoria_id']]);

        $_SESSION['mensaje'] = "Categoría actualizada correctamente ✅";
        header('Location: perfil.php');
        exit();
    } catch (PDOException $e) {
        $error = "Error al actualizar la categoría: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Modificar Categoría - GAP COMPONENTS</title>
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
            <div class="carrito-icono">
                <a href="perfil.php"><img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Abrir Perfil" class="icono-perfil img-circular"></a>
                <a href="logout.php"><img src="recursos/images/LogOut.png" alt="Cerrar Sesión" class="icono-cerrar-sesion"></a>
            </div>
        </nav>
    </header>

    <main>
        <h2>Modificar Categoría</h2>

        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Formulario para seleccionar una categoría -->
        <?php if (!isset($categoria)): ?>
            <form action="modificar_categoria.php" method="GET">
                <div class="form-group">
                    <label for="categoria_id">Selecciona una categoría:</label>
                    <select name="id" id="categoria_id" required>
                        <option value="">--Seleccionar Categoría--</option>
                        <?php foreach ($categorias as $categoria_option): ?>
                            <option value="<?= $categoria_option['id']; ?>">
                                <?= $categoria_option['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Cargar Datos de la Categoría</button>
            </form>
        <?php else: ?>
            
            <!-- Formulario para modificar una categoría -->
            <form action="modificar_categoria.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="categoria_id" value="<?= $categoria['id'] ?>">
                <input type="hidden" name="imagen_actual" value="<?= $categoria['imagen'] ?>">

                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" value="<?= $categoria['nombre'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" id="descripcion" required><?= $categoria['descripcion'] ?></textarea>
                </div>
                <div class="form-group">
                    <label for="categoria_padre">Categoría Padre:</label>
                    <select name="categoria_padre" id="categoria_padre">
                        <option value="">Sin Categoría Padre</option>
                        <?php foreach ($categorias as $categoria_option): ?>
                            <?php if ($categoria_option['id'] != $categoria['id']): ?> <!-- Evitar selección a sí misma -->
                                <option value="<?= $categoria_option['id']; ?>" <?= ($categoria['categoria_padre'] == $categoria_option['id']) ? 'selected' : '' ?>>
                                    <?= $categoria_option['nombre']; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="imagen">Imagen:</label>
                    <input type="file" name="imagen" id="imagen">
                    <p>Imagen actual: <?= $categoria['imagen'] ?></p>
                </div>
                <button type="submit">Modificar Categoría</button>
            </form>
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

