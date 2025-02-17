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

// Verificar si se pasó el pedido_id
if (!isset($_GET['pedido_id'])) {
    die("ID del pedido no especificado.");
}

$pedido_id = intval($_GET['pedido_id']); 

try {
    // Consulta para obtener los detalles del pedido
        $stmt = $db->prepare("
        SELECT p.id AS producto_id, 
            p.nombre AS producto, 
            dp.cantidad, 
            dp.precio, 
            (dp.cantidad * dp.precio) AS subtotal
        FROM detalle_pedido dp
        JOIN productos p ON dp.producto_id = p.id
        WHERE dp.pedido_id = :pedido_id
    ");
    $stmt->execute(['pedido_id' => $pedido_id]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener detalles del pedido: " . $e->getMessage());
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

        <div class="detalles-pedido-pedidos">
            <h1>Detalles del Pedido #<?= htmlspecialchars($pedido_id) ?></h1>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="col-ocultar">Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Valoración</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="4">No hay artículos en este pedido.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?= htmlspecialchars($producto['producto']) ?></td>
                                <td class="col-ocultar"><?= htmlspecialchars($producto['cantidad']) ?></td>
                                <td><?= htmlspecialchars(number_format($producto['precio'], 0)) ?> €</td>
                                <td>
                                    <?php
                                    $stmt = $db->prepare("SELECT valor FROM valoraciones WHERE producto_id = :producto_id AND usuario_id = :usuario_id");
                                    $stmt->execute([
                                        'producto_id' => $producto['producto_id'],
                                        'usuario_id' => $_SESSION['usuario_id']
                                    ]);
                                    $valoracion = $stmt->fetch(PDO::FETCH_ASSOC);
                                    if ($valoracion): ?>
                                        <span>Valorado: <?= htmlspecialchars($valoracion['valor']) ?> estrellas</span>
                                    <?php else: ?>
                                        <form method="post" action="guardar_valoracion.php">
                                            <input type="hidden" name="producto_id" value="<?= htmlspecialchars($producto['producto_id']); ?>">
                                            <input type="hidden" name="pedido_id" value="<?= htmlspecialchars($pedido_id); ?>">
                                            <input type="hidden" name="usuario_id" value="<?= htmlspecialchars($_SESSION['usuario_id']); ?>">
                                            <ul class="estrellas">
                                                <li data-valor="1">&#9733;</li>
                                                <li data-valor="2">&#9733;</li>
                                                <li data-valor="3">&#9733;</li>
                                                <li data-valor="4">&#9733;</li>
                                                <li data-valor="5">&#9733;</li>
                                            </ul>
                                            <input type="hidden" name="valor" id="valoracion" required>
                                            <button type="submit">Valorar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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

<script>
document.querySelectorAll('.estrellas').forEach(ul => {
    const estrellas = ul.querySelectorAll('li');
    const input = ul.parentElement.querySelector('#valoracion');
    let valorSeleccionado = 0; // Guardar el valor seleccionado por clic

    estrellas.forEach((estrella, index) => {
        // Evento hover: iluminar hasta la estrella actual
        estrella.addEventListener('mouseover', () => {
            resetEstrellas(estrellas); // Limpia todas las estrellas
            marcarEstrellasHasta(estrellas, index); // Ilumina hasta la estrella actual
        });

        // Evento clic: selecciona la estrella y guarda el valor
        estrella.addEventListener('click', () => {
            valorSeleccionado = index + 1; // Guarda el valor seleccionado
            resetEstrellas(estrellas); // Limpia todas las estrellas
            marcarEstrellasHasta(estrellas, index); // Ilumina hasta la seleccionada
            input.value = valorSeleccionado; // Actualiza el input oculto
        });
    });

    // Evento salir del área de estrellas: muestra la selección actual
    ul.addEventListener('mouseleave', () => {
        resetEstrellas(estrellas); // Limpia todas las estrellas
        if (valorSeleccionado > 0) {
            marcarEstrellasHasta(estrellas, valorSeleccionado - 1); // Ilumina las seleccionadas
        }
    });
});

// Función para quitar la clase seleccionada de todas las estrellas
function resetEstrellas(estrellas) {
    estrellas.forEach(estrella => estrella.classList.remove('seleccionada'));
}

// Función para iluminar hasta un índice específico
function marcarEstrellasHasta(estrellas, index) {
    for (let i = 0; i <= index; i++) {
        estrellas[i].classList.add('seleccionada');
    }
}
</script>

</body>
</html>
