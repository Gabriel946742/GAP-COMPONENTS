<?php
session_start();
require 'conexion.php';

$db = conectaDB('mi_tienda_online');

// Obtener la foto de perfil del usuario logueado
$foto_perfil = 'recursos/images/perfil.png'; // Imagen por defecto
$stmt = $db->prepare("SELECT foto FROM usuarios WHERE id = :usuario_id");
$stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario && !empty($usuario['foto']) && file_exists("recursos/images/" . $usuario['foto'])) {
    $foto_perfil = 'recursos/images/' . $usuario['foto'];
}

// Verificar si el usuario es administrador
$is_admin = $_SESSION['usuario_tipo'] === 'administrador';

// Procesar el formulario de eliminación de categoría
if ($is_admin && isset($_POST['eliminar_categoria'])) {
    $categoria_id = $_POST['categoria_id'];

    $stmt = $db->prepare("DELETE FROM categorias WHERE id = :id");
    $stmt->execute(['id' => $categoria_id]);
    $_SESSION['mensaje'] = "Categoría eliminada correctamente";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Eliminar Categoría - GAP COMPONENTS</title>
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
        <h3>Eliminar Categoría</h3>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje-success">
                <p><?= $_SESSION['mensaje']; ?></p>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <form action="eliminar_categoria.php" method="POST">
            <div class="form-group">
                <label for="categoria_id">Selecciona una categoría para eliminar:</label>
                <br></br>
                <select name="categoria_id" id="categoria_id" required>
                    <option value="">Selecciona una categoría</option>
                    <?php
                    $stmt = $db->query("SELECT id, nombre FROM categorias");
                    while ($categoria = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= $categoria['id']; ?>"><?= $categoria['nombre']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" name="eliminar_categoria">Eliminar Categoría</button>
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
