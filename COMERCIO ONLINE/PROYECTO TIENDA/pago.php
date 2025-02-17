<?php
session_start();
require 'conexion.php';
$db = conectaDB('mi_tienda_online');

// Inicializar carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Verifica si hay productos en el carrito
$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

// Recuperar datos del carrito desde la base de datos
foreach ($carrito as $key => $producto) {
    $stmt = $db->prepare("SELECT id, nombre, precio, imagen FROM productos WHERE id = :id");
    $stmt->execute(['id' => $producto['id']]);
    $producto_db = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto_db) {
        $_SESSION['carrito'][$key]['nombre'] = $producto_db['nombre'];
        $_SESSION['carrito'][$key]['precio'] = $producto_db['precio'];
        $_SESSION['carrito'][$key]['imagen'] = $producto_db['imagen'];
    }
}

$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

// Calcular el total del carrito
$total_carrito = 0;
foreach ($_SESSION['carrito'] as $producto) {
    $total_carrito += $producto['cantidad'] * $producto['precio'];
}

// Eliminar un producto del carrito
if (isset($_GET['eliminar'])) {
    $producto_id = $_GET['eliminar'];
    unset($_SESSION['carrito'][$producto_id]);
    header('Location: pago.php');
    exit();
}

// Obtener la foto de perfil del usuario logueado
$foto_perfil = 'recursos/images/perfil.png';
if (isset($_SESSION['usuario_id'])) {
    $stmt = $db->prepare("SELECT foto FROM usuarios WHERE id = :usuario_id");
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && !empty($usuario['foto'])) {
        $foto_perfil = 'recursos/images/' . $usuario['foto'];
    }
}

// Procesar el formulario de pago
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $nombre = htmlspecialchars($_POST['nombre']);
    $direccion = htmlspecialchars($_POST['direccion']);
    $codigo_postal = htmlspecialchars($_POST['codigo_postal']);
    $localidad = htmlspecialchars($_POST['localidad']);
    $provincia = htmlspecialchars($_POST['provincia']);
    $pais = htmlspecialchars($_POST['pais']);
    $dni = htmlspecialchars($_POST['dni']);
    $metodo_pago = htmlspecialchars($_POST['metodo_pago']);

    // Validar método de pago
    $metodos_validos = ['tarjeta', 'paypal', 'bizum'];
    if (!in_array($metodo_pago, $metodos_validos)) {
        die("Método de pago no válido.");
    }

    $fecha_pedido = date("Y-m-d H:i:s");
    $estado = "Pendiente";

    // Insertar el pedido
    $stmt = $db->prepare("INSERT INTO pedidos 
        (usuario_id, fecha_pedido, total, estado, direccion, codigo_postal, localidad, provincia, pais, DNI) 
        VALUES 
        (:usuario_id, :fecha_pedido, :total, :estado, :direccion, :codigo_postal, :localidad, :provincia, :pais, :DNI)");
    $stmt->execute([
        'usuario_id' => $usuario_id,
        'fecha_pedido' => $fecha_pedido,
        'total' => $total_carrito,
        'estado' => $estado,
        'direccion' => $direccion,
        'codigo_postal' => $codigo_postal,
        'localidad' => $localidad,
        'provincia' => $provincia,
        'pais' => $pais,
        'DNI' => $dni
    ]);

    $pedido_id = $db->lastInsertId();

    // Insertar detalles del pedido
    foreach ($carrito as $producto) {
        $stmt = $db->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio) 
                              VALUES (:pedido_id, :producto_id, :cantidad, :precio)");
        $stmt->execute([
            'pedido_id' => $pedido_id,
            'producto_id' => $producto['id'],
            'cantidad' => $producto['cantidad'],
            'precio' => $producto['precio']
        ]);
    }

    // Vaciar el carrito y redirigir a la confirmación
    $_SESSION['carrito'] = [];
        switch ($metodo_pago) {
            case 'tarjeta':
                header("Location: tarjeta.php?pedido_id=" . $pedido_id);
                break;
            case 'paypal':
                header("Location: paypal.php?pedido_id=" . $pedido_id);
                break;
            case 'bizum':
                header("Location: bizum.php?pedido_id=" . $pedido_id);
                break;
            default:
                header("Location: confirmacion.php?pedido_id=" . $pedido_id);
                break;
        }

    $_SESSION['mensaje'] = "¡Pedido realizado con éxito! Tu pedido #$pedido_id se está procesando.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Pago - GAP COMPONENTS</title>
    <link rel="stylesheet" href="recursos/css/style.css">
</head>
<body>

<div class="main-container">
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
            <a href="logout.php"><img src="recursos/images/LogOut.png" alt="Cerrar Sesión" class="icono-cerrar-sesion"></a>
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
        <div class="container">
            <!-- Formulario de facturación -->
            <div class="billing-form">
                <h2>Detalles de facturación</h2>
                <form action="pago.php" method="POST">
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="apellidos">Apellidos *</label>
                        <input type="text" id="apellidos" name="apellidos" required>
                    </div>
                    <div class="form-group">
                        <label for="dni">DNI/NIF *</label>
                        <input type="text" id="dni" name="dni" required>
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección *</label>
                        <input type="text" id="direccion" name="direccion" required>
                    </div>
                    <div class="form-group">
                        <label for="codigo_postal">Código Postal *</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" required>
                    </div>
                    <div class="form-group">
                        <label for="localidad">Localidad *</label>
                        <input type="text" id="localidad" name="localidad" required>
                    </div>
                    <div class="form-group">
                        <label for="provincia">Provincia *</label>
                        <input type="text" id="provincia" name="provincia" required>
                    </div>
                    <div class="form-group">
                        <label for="pais">Pais *</label>
                        <input type="text" id="pais" name="pais" required>
                    </div>

                    <!-- Método de pago -->
                    <div class="metodos-pago-resumen">
                        <h2>Selecciona tu método de pago</h2>
                        <div class="opciones-pago">
                            <label class="metodo">
                            <input type="radio" name="metodo_pago" value="tarjeta" checked>
                            <div class="icono">
                                <img src="recursos/images/redsys.png" alt="Tarjeta de crédito/débito">
                                <span>Tarjeta</span>
                            </div>
                            </label>
                            <label class="metodo">
                            <input type="radio" name="metodo_pago" value="paypal">
                            <div class="icono">
                                <img src="recursos/images/logo-paypal.png" alt="PayPal">
                                <span>PayPal</span>
                            </div>
                            </label>
                            <label class="metodo">
                            <input type="radio" name="metodo_pago" value="bizum">
                            <div class="icono">
                                <img src="recursos/images/bizum.png" alt="Transferencia bancaria"></a>
                                <span>Bizum</span>
                            </div>
                            </label>
                        </div>
                    </div>
                    <div class="texto-datos">
                        <h2>
                            Tus datos personales se utilizarán para procesar tu pedido, mejorar tu experiencia en esta web y otros propósitos 
                            descritos en nuestra política de privacidad.
                        </h2>
                    </div>
                        <button type="submit" class="boton-pago">Realizar Pedido</button>
                </form>
            </div>

            <!-- Resumen del carrito -->
            <div class="cart-summary">
                <h2>Tu pedido</h2>
                <?php if (count($carrito) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Producto</th>
                                <th class="col-ocultar">Cantidad</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carrito as $producto): ?>
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
                                    <td class="col-ocultar"><?= htmlspecialchars($producto['cantidad']) ?></td>
                                    <td><?= number_format($producto['cantidad'] * $producto['precio'], 2) ?> €</td>
                                    <td>
                                    <div class="boton-eliminar-carrito">
                                        <a href="pago.php?eliminar=<?= $key ?>">X</a>
                                    </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="total-container">
                        <h3>Total:</h3>
                        <div class="right-side">
                            <h3><?= number_format($total_carrito, 2) ?> €</h3>
                        </div>
                    </div>
                    <div class="iva-pago">
                        <small><p>(Incluye <span class="iva-result"> <?= 0.21*htmlspecialchars($total_carrito) ?> €</span> IVA)</p></small>
                    </div>
                <?php else: ?>
                    <div class="carrito-vacio-pago">
                        <img src="recursos/images/carro-vacio.png" alt="Carrito-Vacio" class="icono-carrito-vacio">
                        <p>Tu carrito está vacío. ¡Agrega productos para continuar!</p>
                        <a href="productos.php"><button type="submit">Volver a la tienda</button></a>
                    </div>
                <?php endif; ?>
            </div>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const mensaje = document.querySelector('.mensaje-flotante');
        if (mensaje) {
            mensaje.classList.add('visible'); 
            setTimeout(() => {
                mensaje.classList.remove('visible'); 
            }, 10000);
        }
    });
</script>

</div>

</body>
</html>
