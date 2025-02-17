<?php
session_start();
require 'conexion.php';
$db = conectaDB('mi_tienda_online');

// Mensaje de sesión
$mensaje = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

// Obtener la foto de perfil del usuario logueado
$foto_perfil = 'recursos/images/perfil.png'; 
if (isset($_SESSION['usuario_id'])) {
    $stmt = $db->prepare("SELECT foto FROM usuarios WHERE id = :usuario_id");
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si la foto existe en la base de datos y no está vacía
    if ($usuario && !empty($usuario['foto'])) {
        // Construir la ruta completa de la imagen
        $foto_perfil = 'recursos/images/' . $usuario['foto'];
    }
}

// Consulta para obtener los productos destacados
$stmt = $db->prepare("
    SELECT p.id, p.nombre, p.precio, p.stock, p.imagen
    FROM productos_estrella pe
    JOIN productos p ON pe.producto_id = p.id
");
$stmt->execute();
$productos_estrella = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías
$stmt = $db->query("SELECT * FROM categorias");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>GAP COMPONENTS</title>
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
    <div class="categorias-titulo">
    <h2>Categorías</h2>
    </div>
    <div class="categorias-carousel">
        <button class="carousel-btn prev" aria-label="Anterior">‹</button>
        <div class="categorias-container">
            <?php foreach ($categorias as $categoria): ?>
                <div class="categoria">
                    <a href="productos.php?categoria_id=<?= $categoria['id'] ?>">
                        <img src="recursos/images/<?= htmlspecialchars($categoria['imagen']) ?>" 
                        alt="<?= htmlspecialchars($categoria['nombre']) ?>">
                        <h3><?= htmlspecialchars($categoria['nombre']) ?></h3>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-btn next" aria-label="Siguiente">›</button>
    </div>
    <div class="destacados-titulo">
    <h2>Productos Destacados</h2>
    </div>
    <div class="productos">
        <?php if (!empty($productos_estrella)): ?>
            <?php foreach ($productos_estrella as $producto): ?>
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
                    No se encontraron productos destacados
                </p>
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

<div id="cookie-banner" class="cookie-banner">
    <p>Este sitio web utiliza cookies para mejorar la experiencia del usuario. Al continuar navegando, aceptas su uso. 
    <a href="politica-de-cookies.html">Más información</a></p>
    <button id="accept-cookies" class="btn-accept">Aceptar</button>
</div>

<!-- JavaScript para efecto de fade in/out en el mensaje -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const mensaje = document.querySelector('.mensaje-flotante');
        if (mensaje) {
            setTimeout(() => {
                mensaje.classList.add('visible');
            }, 100); 

            setTimeout(() => {
                mensaje.classList.remove('visible');
            }, 3000);
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
   
<!-- JavaScript para efecto de carrousel categorias -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const container = document.querySelector(".categorias-container");
        const btnNext = document.querySelector(".carousel-btn.next");
        const btnPrev = document.querySelector(".carousel-btn.prev");

        let scrollAmount = 0;
        const scrollStep = container.offsetWidth / 3; // Mover 1 categoría (25% del ancho)

        btnNext.addEventListener("click", () => {
            const maxScroll = container.scrollWidth - container.offsetWidth;
            scrollAmount = Math.min(scrollAmount + scrollStep, maxScroll);
            container.style.transform = `translateX(-${scrollAmount}px)`;
        });

        btnPrev.addEventListener("click", () => {
            scrollAmount = Math.max(scrollAmount - scrollStep, 0);
            container.style.transform = `translateX(-${scrollAmount}px)`;
        });
    });
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

<script>
        // Seleccionamos el contenedor del carrusel
        const container = document.querySelector('.categorias-container');

        // Variables para controlar el arrastre
        let isDragging = false;
        let startX = 0;
        let scrollLeft = 0;

        // Para dispositivos con ratón (escritorio)
        container.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.pageX - container.offsetLeft; // Guardamos la posición inicial del ratón
            scrollLeft = container.scrollLeft; // Guardamos la posición inicial de desplazamiento
            container.style.cursor = 'grabbing'; // Cambia el cursor a 'grabbing' mientras arrastras
        });

        container.addEventListener('mouseleave', () => {
            isDragging = false;
            container.style.cursor = 'grab'; // Cambia el cursor a 'grab' cuando el ratón sale
        });

        container.addEventListener('mouseup', () => {
            isDragging = false;
            container.style.cursor = 'grab'; // Cambia el cursor a 'grab' cuando dejas de arrastrar
        });

        container.addEventListener('mousemove', (e) => {
            if (!isDragging) return; // Si no se está arrastrando, no hacemos nada
            const x = e.pageX - container.offsetLeft;
            const walk = (x - startX) * 3; // Cambia la velocidad de desplazamiento ajustando el valor
            container.scrollLeft = scrollLeft - walk; // Calcula el nuevo desplazamiento
        });

        // Para dispositivos táctiles (móviles y tabletas)
        let startTouchX = 0;

        container.addEventListener('touchstart', (e) => {
            isDragging = true;
            startTouchX = e.touches[0].clientX; // Guardamos la posición inicial del toque
            scrollLeft = container.scrollLeft; // Guardamos la posición inicial de desplazamiento
        });

        container.addEventListener('touchend', () => {
            isDragging = false;
        });

        container.addEventListener('touchmove', (e) => {
            if (!isDragging) return; // Si no se está tocando, no hacemos nada
            const deltaX = startTouchX - e.touches[0].clientX; // Calcula el desplazamiento horizontal
            container.scrollLeft = scrollLeft + deltaX; // Calcula el nuevo desplazamiento en base al movimiento del dedo
            startTouchX = e.touches[0].clientX; // Actualiza la posición del toque
        });

</script>


</body>
</html>






