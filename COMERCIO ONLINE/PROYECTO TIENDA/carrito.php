<?php
session_start();
require 'conexion.php';
$db = conectaDB('mi_tienda_online');

// Redirigir al registro si el usuario no está logueado
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['mensaje'] = "Debes iniciar sesión o registrarte antes de procesar un pedido";
    header("Location: login.php");
    exit();
}

// Inicializar carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Verifica si hay productos en el carrito
$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

// Recuperar datos del carrito desde la base de datos
foreach ($carrito as $key => $producto) {
    $stmt = $db->prepare("SELECT id, nombre, precio, imagen, stock FROM productos WHERE id = :id");
    $stmt->execute(['id' => $producto['id']]);
    $producto_db = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto_db) {
        $_SESSION['carrito'][$key]['nombre'] = $producto_db['nombre'];
        $_SESSION['carrito'][$key]['precio'] = $producto_db['precio'];
        $_SESSION['carrito'][$key]['imagen'] = $producto_db['imagen'];
        $_SESSION['carrito'][$key]['stock'] = $producto_db['stock'];
    }
}

// Calcular el total del carrito
$total_carrito = 0;
foreach ($_SESSION['carrito'] as $producto) {
    $total_carrito += $producto['cantidad'] * $producto['precio'];
}

// Eliminar un producto del carrito
if (isset($_GET['eliminar'])) {
    $producto_id = $_GET['eliminar'];
    unset($_SESSION['carrito'][$producto_id]);
    header('Location: carrito.php');
    exit();
}

// Obtener la foto de perfil del usuario logueado
$foto_perfil = 'recursos/images/perfil.png'; 
if (isset($_SESSION['usuario_id'])) {
    $stmt = $db->prepare("SELECT foto FROM usuarios WHERE id = :usuario_id");
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si la foto existe en la base de datos y no está vacía
    if ($usuario && !empty($usuario['foto'])) {
        $foto_perfil = 'recursos/images/' . $usuario['foto']; // Ruta correcta
    }
}

// Si se envía el formulario de actualización
if (isset($_POST['actualizar'])) {
    // Verificar que 'producto_id' y 'cantidad' estén definidos
    if (isset($_POST['producto_id']) && isset($_POST['cantidad'])) {
        $producto_id = $_POST['producto_id'];
        $cantidad = $_POST['cantidad'];

        $stmt = $db->prepare("SELECT stock FROM productos WHERE id = :id");
        $stmt->execute(['id' => $producto_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar que la cantidad no supere el stock disponible
        if ($producto && $cantidad > $producto['stock']) {
            $_SESSION['mensaje'] = "No puedes añadir más de {$producto['stock']} unidades al carrito.";
            header("Location: detalles.php?id=" . $producto['id']);
            exit;
        }

        // Actualizar la cantidad del producto en el carrito
        if ($cantidad > 0) {
            $_SESSION['carrito'][$producto_id]['cantidad'] = $cantidad;
        } else {
            // Si la cantidad es menor o igual a 0, eliminamos el producto
            unset($_SESSION['carrito'][$producto_id]);
        }
        header('Location: carrito.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Carrito - GAP COMPONENTS</title>
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
        
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="perfil.php"><img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Abrir Perfil" class="icono-perfil img-circular"></a>
                <a href="logout.php"><img src="recursos/images/LogOut.png" alt="Cerrar Sesión" class="icono-cerrar-sesion"></a> <!-- Enlace para logout -->
            <?php else: ?>
                <a href="login.php"><img src="recursos/images/perfil.png" class="icono-login"></a>
            <?php endif; ?>
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
    <div class="titulo-carrito">
    <h2>Mi Carrito</h2>
    </div>
    <div class="carrito">
        <?php if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Producto</th>
                        <th class="col-ocultar">Cantidad</th>
                        <th class="col-ocultar">Precio</th>
                        <th>Total</th>
                        <th >Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_carrito = 0;
                    foreach ($_SESSION['carrito'] as $producto_id => $producto): 
                        $total_producto = $producto['cantidad'] * $producto['precio'];
                        $total_carrito += $total_producto;
                    ?>
                        <tr>
                            <td>
                                <a href="detalles.php?id=<?= htmlspecialchars($producto['id']) ?>">
                                <img src="recursos/images/<?= htmlspecialchars($producto['imagen'] ?? 'perfil.png') ?>" alt="<?= htmlspecialchars($producto['nombre'] ?? 'Producto sin nombre') ?>" style="width: 70px; height: auto;">
                                </a>
                            </td>
                            <td>
                                <a href="detalles.php?id=<?= htmlspecialchars($producto['id']) ?>">
                                <?= htmlspecialchars($producto['nombre']) ?>
                                </a>
                            </td>
                            <td class="col-ocultar">
                                <form action="carrito.php" method="POST">
                                    <input type="number" name="cantidad" value="<?= htmlspecialchars($producto['cantidad']) ?>" min="1" max="<?= htmlspecialchars($producto['stock'] ?? 1) ?>">
                                    <input type="hidden" name="producto_id" value="<?= htmlspecialchars($producto_id) ?>">
                                    <button type="submit" name="actualizar">Actualizar</button>
                                    
                                </form>
                            </td>
                            <td class="col-ocultar"><?= htmlspecialchars($producto['precio']) ?> € </td>
                            <td><?= htmlspecialchars($total_producto) ?> € </td>
                            <td>
                                <div class="boton-eliminar-carrito">
                                    <a href="carrito.php?eliminar=<?= $producto_id ?>">X</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>TOTAL:  <?= htmlspecialchars($total_carrito) ?> € </h3>
            <small><p>(Incluye <span class="iva-result"> <?= 0.21*htmlspecialchars($total_carrito) ?> €</span> IVA)</p></small>
            <a href="pago.php"><button type="submit">Proceder al pago</button></a>
        <?php else: ?>
            <img src="recursos/images/carro-vacio.png" alt="Carrito-Vacio" class="icono-carrito-vacio">
            <p>Tu carrito está vacío. ¡Agrega productos para continuar!</p>
            <a href="productos.php"><button type="submit">Volver a la tienda</button></a>
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

