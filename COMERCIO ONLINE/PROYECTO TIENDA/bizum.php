<?php
session_start();
require 'conexion.php';
$db = conectaDB('mi_tienda_online');

// Obtener productos destacados
$stmt = $db->query("SELECT p.id, p.nombre, p.precio, p.imagen FROM productos p
                    JOIN productos_estrella pe ON p.id = pe.producto_id");
$productos_destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías
$stmt = $db->query("SELECT * FROM categorias");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mensaje de sesión
$mensaje = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

// Si el usuario está logueado, obtener su foto de perfil desde la base de datos
$foto_perfil = 'recursos/images/perfil.png'; 
if (isset($_SESSION['usuario_id'])) {
    $stmt = $db->prepare("SELECT foto FROM usuarios WHERE id = :usuario_id");
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si la foto existe en la base de datos y no está vacía
    if ($usuario && !empty($usuario['foto'])) {
        // Construir la ruta completa de la imagen
        $foto_perfil = 'recursos/images/' . $usuario['foto']; // Ruta correcta
    }
}

$id_pedido = "No hay pedidos disponibles.";
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];

    // Consulta para obtener el último pedido del usuario
    $stmt = $db->prepare("SELECT id FROM pedidos WHERE usuario_id = :usuario_id ORDER BY id DESC LIMIT 1");
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();

    // Recuperar el ID del pedido si existe
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pedido) {
        $id_pedido = $pedido['id'];
    }
}

// Obtener el id del último pedido del usuario
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];

    // Consulta para obtener el último pedido
    $stmt = $db->prepare("SELECT id, total FROM pedidos WHERE usuario_id = :usuario_id ORDER BY id DESC LIMIT 1");
    $stmt->execute(['usuario_id' => $usuario_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pedido) {
        // Obtener el total del último pedido
        $total_pago = $pedido['total'];  // Suponiendo que la tabla 'pedidos' tiene una columna 'total'
    } else {
        $total_pago = 0;
    }
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
<div class="bizum">
    <div class="container-bizum">
            <h1>Pago con Bizum</h1>
            <img src="recursos/images/bizum.png">
            <p><strong class="precio-grande"><?php echo number_format($total_pago, 2, ',', '.'); ?> €</strong><br></p>
            <p>Para completar tu pedido, realiza un Bizum al número <br> <span class="highlight">+34 605 936 706</span><br><br>
                En el mensaje de tu Bizum, indica tu número de pedido: <strong>#<?php echo htmlspecialchars($id_pedido); ?></strong>
            </span><br>
                Una vez recibido el pago, procesaremos tu pedido de inmediato.
                Puedes ver las actualizaciones sobre tu pedido en el apartado <a href="pedidos.php"><strong>Mis Pedidos</strong></a> 
                en tu perfil de usuario.
            </p>
            <p>
                Si tienes alguna duda, puedes contactarnos por Whatsapp al <br></br> <span class="highlight">605 936 706</span><br></br>
                 o enviarnos un correo a <br></br> <span class="highlight">gabriel946742@gmail.com</span>
            </p>
            <button onclick="volverInicio()">Volver a la tienda</button>
        </div>
</div>

    <script>
        function volverInicio() {
            window.location.href = "index.php"; // Cambia a la página de inicio o carrito
        }
    </script>
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
    <p>Este sitio web utiliza cookies para mejorar la experiencia del usuario. Al continuar navegando, aceptas su uso. <a href="politica-de-cookies.html">Más información</a></p>
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
    // Establece el tiempo en milisegundos (1 día = 24 * 60 * 60 * 1000)
    const COOKIE_TIMEOUT = 24 * 60 * 60 * 1000;  // 1 día en milisegundos

    // Función para comprobar si ya se ha aceptado las cookies
    function hasAcceptedCookies() {
        return localStorage.getItem('cookieAccepted') === 'true';
    }

    // Muestra el banner solo si no se ha aceptado las cookies
    function showCookieBanner() {
        if (!hasAcceptedCookies()) {
            const banner = document.getElementById('cookie-banner');
            banner.style.display = 'block';  // Muestra el banner
        }
    }

    // Guarda la aceptación de cookies en localStorage
    function acceptCookies() {
        localStorage.setItem('cookieAccepted', 'true');
        const banner = document.getElementById('cookie-banner');
        banner.style.display = 'none';  // Oculta el banner después de aceptar
    }

    // Evento para el botón de aceptación
    document.getElementById('accept-cookies').addEventListener('click', acceptCookies);

    // Ejecutar al cargar la página
    window.onload = showCookieBanner;

</script>


</body>
</html>
