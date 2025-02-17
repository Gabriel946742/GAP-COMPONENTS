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

if ($usuario && !empty($usuario['foto']) && file_exists("recursos/images/" . $usuario['foto'])) {
    $foto_perfil = "recursos/images/" . $usuario['foto'];
}

// Verificar si el usuario es administrador
$is_admin = $_SESSION['usuario_tipo'] === 'administrador';

try {
    // Consulta para obtener los pedidos del usuario autenticado
    $stmt = $db->prepare("SELECT id, fecha_pedido, total, estado, seguimiento FROM pedidos WHERE usuario_id = :usuario_id");
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtenemos todos los resultados

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
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
         
        <div class="pedidos-resumen">
        <h1>Historial de Pedidos</h1>
        <?php if (empty($pedidos)): ?>
            <p style="text-align: center; color: #666;">No tienes pedidos realizados.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th class="col-ocultar">Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="col-ocultar">Seguimiento</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($pedido['id']); ?></td>
                            <td class="col-ocultar"><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($pedido['total'], 0)); ?> €</td>
                            <td>
                                <span class="estado <?php echo htmlspecialchars($pedido['estado']); ?>">
                                    <?php echo htmlspecialchars($pedido['estado']); ?>
                                </span>
                            </td>
                            <td class="col-ocultar"><?= htmlspecialchars($pedido['seguimiento'] ?? 'N/A') ?></td>
                            <td>
                                <a href="detalles_pedido.php?pedido_id=<?= $pedido['id'] ?>">Ver Detalles</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        </div>
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
