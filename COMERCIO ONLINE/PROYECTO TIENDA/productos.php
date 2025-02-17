<?php
session_start();
require 'conexion.php';
$db = conectaDB('mi_tienda_online');

// Paginación
$productos_por_pagina = 12;
$pagina_actual = isset($_GET['pagina']) ? max((int)$_GET['pagina'], 1) : 1;
$offset = ($pagina_actual - 1) * $productos_por_pagina;

// Obtener el término de búsqueda
$busqueda = isset($_GET['query']) ? trim($_GET['query']) : null;

// Filtrar productos por categoría o búsqueda con paginación
$sql = "SELECT * FROM productos";
$parametros = [
    'limite' => $productos_por_pagina,
    'offset' => $offset
];

if (!empty($busqueda)) {
    $sql .= " WHERE nombre LIKE :busqueda";
    $parametros['busqueda'] = '%' . $busqueda . '%';
} elseif (!empty($_GET['categoria_id'])) {
    $sql .= " WHERE categoria_id = :categoria_id";
    $parametros['categoria_id'] = $_GET['categoria_id'];
}

$sql .= " LIMIT :limite OFFSET :offset";

$stmt = $db->prepare($sql);

// Enlazar parámetros asegurando que :limite y :offset sean enteros
$stmt->bindValue(':limite', $parametros['limite'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $parametros['offset'], PDO::PARAM_INT);

// Enlazar otros parámetros si existen
if (!empty($busqueda)) {
    $stmt->bindValue(':busqueda', $parametros['busqueda']);
} elseif (!empty($_GET['categoria_id'])) {
    $stmt->bindValue(':categoria_id', $parametros['categoria_id']);
}

$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar total de productos para la paginación
$sql_total = "SELECT COUNT(*) FROM productos";
if (!empty($busqueda)) {
    $sql_total .= " WHERE nombre LIKE :busqueda";
} elseif (!empty($_GET['categoria_id'])) {
    $sql_total .= " WHERE categoria_id = :categoria_id";
}

$stmt_total = $db->prepare($sql_total);

// Enlazar parámetros para el conteo
if (!empty($busqueda)) {
    $stmt_total->bindValue(':busqueda', $parametros['busqueda']);
} elseif (!empty($_GET['categoria_id'])) {
    $stmt_total->bindValue(':categoria_id', $parametros['categoria_id']);
}

$stmt_total->execute();
$total_productos = (int)$stmt_total->fetchColumn();
$total_paginas = ceil($total_productos / $productos_por_pagina);

// Manejo del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id'])) {
    $producto_id = (int)$_POST['producto_id'];
    $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

    $stmt = $db->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->execute(['id' => $producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        if ($cantidad > $producto['stock']) {
            $_SESSION['mensaje'] = "No puedes añadir más de {$producto['stock']} unidades al carrito.";
        } else {
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }

            $producto_en_carrito = false;
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['id'] == $producto_id) {
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

            $_SESSION['mensaje'] = "Producto añadido al carrito";
        }
    } else {
        $_SESSION['mensaje'] = "Producto no encontrado.";
    }

    header("Location: productos.php");
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

// Obtener la categoría seleccionada desde la URL (si existe)
$categoria_id = isset($_GET['categoria_id']) ? $_GET['categoria_id'] : null;

if ($categoria_id) {
    try {
        $stmt = $db->prepare("SELECT nombre, categoria_padre FROM categorias WHERE id = :categoria_id");
        $stmt->execute(['categoria_id' => $categoria_id]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($categoria) {
            $categoria_nombre = $categoria['nombre'];
            $categoria_padre_id = $categoria['categoria_padre'];

            // Si la categoría tiene una categoría padre, obtener su nombre
            if ($categoria_padre_id) {
                $stmt_padre = $db->prepare("SELECT nombre FROM categorias WHERE id = :categoria_padre_id");
                $stmt_padre->execute(['categoria_padre_id' => $categoria_padre_id]);
                $categoria_padre = $stmt_padre->fetch(PDO::FETCH_ASSOC);

                $categoria_padre_nombre = $categoria_padre ? $categoria_padre['nombre'] : "Categoría padre no encontrada";
            } else {
                $categoria_padre_nombre = null; 
            }
        } else {
            $categoria_nombre = "Categoría no encontrada";
            $categoria_padre_nombre = null;
        }
    } catch (PDOException $e) {
        $categoria_nombre = "Error al obtener la categoría";
        $categoria_padre_nombre = null;
    }
} else {
    $categoria_nombre = null;
    $categoria_padre_nombre = null;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Productos - GAP COMPONENTS</title>
    <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="recursos/css/style.css">
</head>
<body>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="mensaje-flotante"><?= htmlspecialchars($_SESSION['mensaje']) ?></div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>
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

    <div class="header-promo">
        <div class="promo-container">
            <a id="promo-text">📦 ENVIOS EN 24/48 HORAS 📦</a>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje-flotante"><?= $mensaje ?></div>
    <?php endif; ?>

    <main>
        <h2>
            <?php if (!empty($busqueda)): ?>
                Resultados para "<?= htmlspecialchars($busqueda) ?>"
            <?php elseif (!empty($_GET['categoria_id'])): ?>
                Productos para "<?= htmlspecialchars($categoria_nombre) ?>"
                <?php if (!empty($categoria_padre_nombre)): ?>
                    (en <?= htmlspecialchars($categoria_padre_nombre) ?>)
                <?php endif; ?>
            <?php else: ?>
                PRODUCTOS
            <?php endif; ?>
        </h2>


        <div class="productos">
        <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $producto): ?>
                <div class="producto">
                    <a href="detalles.php?id=<?= $producto['id'] ?>">
                        <img src="recursos/images/<?= htmlspecialchars($producto['imagen']) ?>" 
                        alt="<?= htmlspecialchars($producto['nombre']) ?>"/>
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
                    <p><img src="recursos/images/exclamacion_buena.png" alt="Icono-error" class="icono-error"> 
                    No se encontraron productos que coincidan con tu selección</p>
                </div>
            </div>
        <?php endif; ?>
        </div>

        <!-- Navegación de paginación -->
        <div class="paginacion">
            <?php if ($pagina_actual > 1): ?>
                <a href="productos.php?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])) ?>">Anterior</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="productos.php?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>" 
                <?= $i === $pagina_actual ? 'class="activo"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="productos.php?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])) ?>">Siguiente</a>
            <?php endif; ?>
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

<!-- JavaScript para efecto de fade in/out -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const mensaje = document.querySelector('.mensaje-flotante');
        if (mensaje) {
            setTimeout(() => mensaje.classList.add('visible'), 100); 
            setTimeout(() => mensaje.classList.remove('visible'), 3000); 
        }
    });
</script>

<!-- JavaScript para efecto de barra anuncios -->
<script>
        const mensajes = [
            "📦 ENVIOS EN 24/48 HORAS 📦",
            "🔥 GRANDES DESCUENTOS 🔥",
            "🌟 DEVOLUCIONES GRATUITAS 🌟",
        ];

        let index = 0;
        const promoText = document.getElementById("promo-text");

        function cambiarMensaje() {
            
            promoText.classList.remove("visible");

            
            setTimeout(() => {
                index = (index + 1) % mensajes.length; 
                promoText.textContent = mensajes[index]; 
                promoText.classList.add("visible"); 
            }, 500); 
        }

        promoText.classList.add("visible");
        setInterval(cambiarMensaje, 4500);
</script>

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

