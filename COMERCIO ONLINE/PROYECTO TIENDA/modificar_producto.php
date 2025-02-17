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

// Obtener la lista de productos para mostrar en el menú desplegable
try {
    $stmt = $db->query("SELECT id, nombre FROM productos");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener los productos: " . $e->getMessage();
}

// Obtener los datos del producto seleccionado si se pasa el ID en la URL (GET)
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    try {
        $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$product_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            $error = "Producto no encontrado.";
        }
    } catch (PDOException $e) {
        $error = "Error al obtener los datos del producto: " . $e->getMessage();
    }
}

// Obtener la lista de categorías
try {
    $stmt = $db->query("SELECT id, nombre FROM categorias");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener las categorías: " . $e->getMessage();
}


// Procesar el formulario de modificación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $descripcion = $_POST['descripcion'];
    $categoria = $_POST['categoria_id'];
    $stock = $_POST['stock'];
    $imagen = $_FILES['imagen']['name'];

    // Si no se seleccionó una nueva imagen, mantener la imagen actual
    if (empty($imagen)) {
        $imagen_path = $_POST['imagen_actual'];  
    } else {
        $imagen_path = basename($imagen);  
        move_uploaded_file($_FILES['imagen']['tmp_name'],'recursos/images/'. $imagen_path);
    }

    try {
        $stmt = $db->prepare("UPDATE productos SET nombre = ?, precio = ?, descripcion = ?, categoria_id = ?, stock = ?, imagen = ? WHERE id = ?");
        $stmt->execute([$nombre, $precio, $descripcion, $categoria, $stock, $imagen_path, $_POST['producto_id']]);

        $_SESSION['mensaje'] = "Producto actualizado correctamente ✅";
        header('Location: perfil.php');
        exit();
    } catch (PDOException $e) {
        $error = "Error al actualizar el producto: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Modificar Producto - GAP COMPONENTS</title>
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
        <h2>Modificar Producto</h2>

        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Formulario para seleccionar un producto -->
        <?php if (!isset($producto)): ?>
            <form action="modificar_producto.php" method="GET">
                <div class="form-group">
                    <label for="producto_id">Selecciona un producto:</label>
                    <br></br>
                    <select name="id" id="producto_id" required>
                        <option value="">--Seleccionar Producto--</option>
                        <?php foreach ($productos as $producto_option): ?>
                            <option value="<?= $producto_option['id']; ?>">
                                <?= $producto_option['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Cargar Datos del Producto</button>
            </form>
        <?php else: ?>

            <!-- Formulario para modificar un producto -->
            <form action="modificar_producto.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                <input type="hidden" name="imagen_actual" value="<?= $producto['imagen'] ?>">

                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" value="<?= $producto['nombre'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="precio">Precio:</label>
                    <input type="text" name="precio" id="precio" value="<?= $producto['precio'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" id="descripcion" required><?= $producto['descripcion'] ?></textarea>
                </div>
                <div class="form-group">
                    <label for="categoria_id">Categoría:</label>
                    <select name="categoria_id" id="categoria_id" required>
                        <option value="">--Seleccionar Categoría--</option>
                        <?php foreach ($categorias as $categoria_option): ?>
                            <option value="<?= $categoria_option['id']; ?>" 
                                <?= ($producto['categoria_id'] == $categoria_option['id']) ? 'selected' : '' ?>>
                                <?= $categoria_option['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stock">Stock:</label>
                    <input type="number" name="stock" id="stock" value="<?= $producto['stock'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="imagen">Imagen: <small>Formatos válidos  .jpg</small></label>
                    <input type="file" name="imagen" id="imagen">
                    <p>Imagen actual: <?= $producto['imagen'] ?></p>
                </div>
                <button type="submit">Modificar Producto</button>
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






