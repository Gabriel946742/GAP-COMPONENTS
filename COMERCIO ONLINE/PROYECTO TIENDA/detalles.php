<?php
session_start();
require 'conexion.php';
$db = conectaDB('mi_tienda_online');

// Obtener el ID del producto desde la URL
$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($product_id) {
    // Obtener los detalles del producto desde la base de datos
    $stmt = $db->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->execute(['id' => $product_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        // Si el producto no existe
        die("Producto no encontrado.");
    }
}

// Si el usuario está logueado, obtener su foto de perfil desde la base de datos
$foto_perfil = 'recursos/images/perfil.png';
if (isset($_SESSION['usuario_id'])) {
    $stmt = $db->prepare("SELECT foto FROM usuarios WHERE id = :usuario_id");
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && !empty($usuario['foto'])) {
        $foto_perfil = 'recursos/images/' . $usuario['foto']; // Ruta correcta
    }
}

// Si se hace clic en el botón de añadir al carrito
if (isset($_POST['add_to_cart'])) {
    $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

    if ($cantidad > $producto['stock']) {
        $_SESSION['mensaje'] = "No puedes añadir más de {$producto['stock']} unidades al carrito.";
        header("Location: detalles.php?id=" . $producto['id']);
        exit;
    }

    if (isset($_SESSION['carrito'])) {
        $producto_en_carrito = false;
        foreach ($_SESSION['carrito'] as &$item) {
            if ($item['id'] == $producto['id']) {
                $item['cantidad'] += $cantidad;
                if ($item['cantidad'] > $producto['stock']) {
                    $item['cantidad'] = $producto['stock'];
                }
                $producto_en_carrito = true;
                break;
            }
        }

        if (!$producto_en_carrito) {
            $_SESSION['carrito'][] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'cantidad' => min($cantidad, $producto['stock']),
            ];
        }
    } else {
        $_SESSION['carrito'] = [
            [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'cantidad' => min($cantidad, $producto['stock']),
            ]
        ];
    }

    $_SESSION['mensaje'] = "Producto añadido al carrito";
    header("Location: detalles.php?id=" . $producto['id']);
    exit;
}

// Mensaje de sesión
$mensaje = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

// Consultar 5 productos aleatorios
$query = "SELECT * FROM productos ORDER BY RAND() LIMIT 4";
$stmt = $db->query($query);

// Verificar si la consulta tiene resultados
$productos_aleatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Detalles del Producto - <?= htmlspecialchars($producto['nombre']) ?></title>
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
                    <input type="text" name="query" placeholder="Buscar productos" required class="input-buscador">
                    <button type="submit" class="btn-buscador">
                        <img src="recursos/images/lupa.png" alt="Buscar" class="icono-buscar">
                    </button>
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

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje-flotante visible"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <main>
    <div class="detalles-box">
        <div class="producto-detalle">
                <div class="producto-imagen">
                    <div class="zoom-container">
                        <img src="recursos/images/<?= htmlspecialchars($producto['imagen']) ?>" 
                        alt="<?= htmlspecialchars($producto['nombre']) ?>" class="zoom-image" />
                    </div>
                </div>

                <div class="producto-info">
                    <h2><?= htmlspecialchars($producto['nombre']) ?></h2>
                    <div class="precio-detalles">
                        <h2> <?= number_format($producto['precio'], 0) ?>€</h2>
                    </div>
                    <div class="stock-disponible">
                        <p><strong>Stock disponible:</strong> <?= $producto['stock'] ?> unidades</p>
                    </div>
                    <div class="estrellas-completo">
                    <p>
                        <?php
                        // Obtener las valoraciones del producto
                        $stmt = $db->prepare("SELECT valor FROM valoraciones WHERE producto_id = :producto_id");
                        $stmt->execute(['producto_id' => $producto['id']]);
                        $valoraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Calcular la media de las valoraciones
                        if (count($valoraciones) > 0) {
                            $suma_valoraciones = array_sum(array_column($valoraciones, 'valor'));
                            $media = $suma_valoraciones / count($valoraciones);
                            $total_valoraciones = count($valoraciones);
                        } else {
                            $media = 0; // Si no hay valoraciones, la media es 0
                            $total_valoraciones = 0;
                        }

                        // Mostrar las estrellas según la media
                        $media = round($media); // Redondear a la estrella más cercana
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $media) {
                                echo '<span class="star filled">&#9733;</span>'; 
                            } else {
                                echo '<span class="star">&#9734;</span>'; 
                            }
                        }

                            echo " ({$total_valoraciones} valoraciones)";
                        ?>
                    </p>
                    </div>

                    <form action="detalles.php?id=<?= $producto['id'] ?>" method="POST">
                        <label for="cantidad">Cantidad:</label>
                        <input type="number" name="cantidad" id="cantidad" value="1" min="1" 
                        max="<?= $producto['stock'] ?>" required>
                        <button type="submit" name="add_to_cart">Añadir al carrito</button>
                    </form>

                    <div class="detalle-envios">
                        <h3>Información de Envíos</h3>
                        <div class="envio-opcion">
                            <div class="columna foto">
                                <img src="recursos/images/logo_gls.png" alt="GLS">
                            </div>
                            <div class="columna lugar">
                                <p>España Peninsular</p>
                            </div>
                            <div class="columna tiempo">
                                <span>1-2 Días</span>
                            </div>
                        </div>
                        <div class="envio-opcion">
                            <div class="columna foto">
                                <img src="recursos/images/logo_correos.png" alt="Correos">
                            </div>
                            <div class="columna lugar">
                                <p>Baleares - Canarias</p>
                            </div>
                            <div class="columna tiempo">
                                <span>2-8 Días</span>
                            </div>
                        </div>
                        <div class="envio-opcion">
                            <div class="columna foto">
                                <img src="recursos/images/logo_gls.png" alt="GLS">
                            </div>
                            <div class="columna lugar">
                                <p>Internacional</p>
                            </div>
                            <div class="columna tiempo">
                                <span>2-8 Días</span>
                            </div>
                        </div>
                    </div>
                    <div class="metodos-pago">
                        <h3>Métodos de pago:</h2>
                        <img src="recursos/images/logos_pago.jpg" alt="Pagos">
                    </div>
                </div>
            </div>
        </div>
        <div class="product-description-box">
            <h3>Descripción</h3>
            <p><?= htmlspecialchars($producto['descripcion']) ?></p>
        </div>
    </div>
    <div class="productos-random-titulo">
        <h2>Podria Interesarte...</h2>
    </div>
    <div class="productos">
        <?php if (!empty($productos_aleatorios)): ?>
            <?php foreach ($productos_aleatorios as $producto): ?>
                <div class="producto">
                    <a href="detalles.php?id=<?= $producto['id'] ?>">
                        <img src="recursos/images/<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>"/>
                    </a>
                    <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                    <p>Precio: <?= number_format($producto['precio'], 2) ?>€</p>
                    <form action="productos.php" method="POST">
                        <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                        <input type="number" name="cantidad" value="1" min="1" max="<?= $producto['stock'] ?>" required>
                        <button type="submit" name="add_to_cart">Añadir al carrito</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results-contenedor">
                <div class="no-results">
                    <p><img src="recursos/images/exclamacion_buena.png" alt="Icono-error" class="icono-error"> No se encontraron productos que coincidan con tu selección</p>
                </div>
            </div>
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

<!-- JavaScript para efecto de fade in/out -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const mensaje = document.querySelector('.mensaje-flotante');
        if (mensaje) {
            setTimeout(() => mensaje.classList.add('visible'), 100); // Fade in
            setTimeout(() => mensaje.classList.remove('visible'), 3000); // Fade out
        }
    });
    
    document.addEventListener("DOMContentLoaded", function () {
    const zoomContainer = document.querySelector(".zoom-container");
    const zoomImage = document.querySelector(".zoom-image");

    zoomContainer.addEventListener("mousemove", function (e) {
        const rect = zoomContainer.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        zoomImage.style.transformOrigin = `${x}% ${y}%`;
    });

    zoomContainer.addEventListener("mouseleave", function () {
        zoomImage.style.transformOrigin = "center";
    });
});

</script>

</body>
</html>


