-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-01-2025 a las 14:59:00
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mi_tienda_online`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria_padre` int(11) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `categoria_padre`, `imagen`) VALUES
(2, 'ORDENADORES', 'Todo tipo de ordenadores de Torre', NULL, 'ordenadores-gaming.jpg'),
(3, 'MOVILES', 'Todo sobre moviles', NULL, 'muchos-iphone.jpg'),
(4, 'CONSOLAS', 'Todo tipo de consolas', NULL, 'Consolas.jpg'),
(15, 'COMPONENTES', 'Todo tipo de componentes informaticos', 2, 'componentes-categoria.webp'),
(16, 'JUEGOS', 'Todo tipo de juegos para consolas y PC', NULL, 'juegos.jpg'),
(17, 'IMPRESORAS', 'Todo tipo de impresoras y productos para su uso', NULL, 'impresora.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio`) VALUES
(17, 14, 4, 1, 199.00),
(18, 14, 9, 2, 150.00),
(19, 14, 11, 1, 799.00),
(20, 15, 4, 1, 199.00),
(21, 15, 9, 1, 150.00),
(22, 15, 20, 2, 1200.00),
(23, 16, 9, 1, 150.00),
(24, 16, 16, 1, 99.00),
(25, 16, 11, 7, 799.00),
(28, 18, 9, 1, 150.00),
(29, 19, 9, 1, 150.00),
(30, 20, 22, 1, 47.00),
(31, 21, 4, 1, 199.00),
(32, 21, 20, 1, 1200.00),
(33, 22, 4, 1, 199.00),
(34, 22, 9, 1, 150.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Enviado','Completado') NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `codigo_postal` varchar(10) NOT NULL,
  `localidad` varchar(100) NOT NULL,
  `provincia` varchar(100) NOT NULL,
  `pais` varchar(100) NOT NULL,
  `DNI` varchar(9) NOT NULL,
  `seguimiento` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `fecha_pedido`, `total`, `estado`, `direccion`, `codigo_postal`, `localidad`, `provincia`, `pais`, `DNI`, `seguimiento`) VALUES
(14, 2, '2024-12-02 00:34:06', 1298.00, 'Enviado', 'Calle ramon gallud 76', '03181', 'Torrevieja', 'Alicante', 'España', '49624265V', NULL),
(15, 2, '2024-12-02 00:45:09', 2749.00, 'Completado', 'Calle ramon gallud 76', '03181', 'Torrevieja', 'Alicante', 'España', '49624265V', NULL),
(16, 2, '2024-12-02 00:46:41', 5842.00, 'Pendiente', 'Calle ramon gallud 76', '03181', 'Torevieja', 'Alicante', 'España', '49624265V', NULL),
(18, 2, '2024-12-03 22:35:36', 150.00, 'Pendiente', 'Calle ramon gallud 76', '03181', 'Torrevieja', 'Alicante', 'España', '49624265V', NULL),
(19, 3, '2024-12-03 23:05:30', 150.00, 'Pendiente', 'Calle ramon gallud 76', '03181', 'Torrevieja', 'Alicante', 'España', '49624265V', NULL),
(20, 2, '2024-12-05 00:58:58', 47.00, 'Completado', 'Calle ramon gallud 76', '03181', 'Torrevieja', 'Alicante', 'España', '49624265V', NULL),
(21, 2, '2024-12-06 10:48:07', 1399.00, 'Pendiente', 'C/Patricio Perez, N°92, 5J', '03181', 'Torrevieja', 'Comunidad Valenciana - Alicante', 'España', '49624265V', NULL),
(22, 2, '2024-12-12 03:22:18', 349.00, 'Pendiente', 'C/Patricio Perez, N°92, 5J', '03181', 'Torrevieja', 'Comunidad Valenciana - Alicante', 'España', '49624265V', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `imagen` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `categoria_id`, `rating`, `imagen`, `stock`) VALUES
(3, 'PS5 PRO', 'Consola PS5 PRO con 2 mandos:\r\n\r\n\r\nProcesador: AMD Ryzen ZEN 2 8 Cores, 16 Threads 3,5 GHz\r\nTarjeta gráfica: AMD Radeon RDNA 2 Based graphics engineup to 2.3 Ghz (10.3 TFLOPS)\r\nMemoria RAM: GDDR6 16 GB 448 GB/S Bandwidth\r\nSSD: 825 GB 5,5 GB/S Raw\r\nOptical Dive: Blu-Ray 4K UHD 66GB/ 100GB\r\nVideo Out: HDMI 2.1, 4K 120 Hz, 8K, VRR\r\nAudio: Tempest 3D Audio engine\r\nPuertos: USB Tipo A (Hi Speed), 2x USB tipo A (Super Speed 10 GB/S), USB Tipo C (10 GB/S)\r\nPotencia: PS5 350 WPS, PS5 Digital 340 WPS', 499.00, 4, 0.00, 'ps5.jpg', 2),
(4, 'PS4 SLIM', 'Consola PS4 con 1 mando y 3 juegos', 199.00, 4, 0.00, 'ps4.jpg', 1),
(9, 'PS1', 'PS1 Clasica con 1 mando y 20 juegos', 150.00, 4, 0.00, 'ps1.jpg', 5),
(11, 'Asus ROG STRIX 2020', 'El portatil gaming mas potente de asus:\r\n-Intel core I9\r\n-32 GB RAM\r\n-RTX 4060 SUPER', 799.00, 2, 0.00, 'ASUS ROG STRIX.webp', 7),
(13, 'IPHONE 15', 'Iphone 15 con todos los extras', 969.00, 3, 0.00, 'iphone 15.jpg', 3),
(14, 'IPHONE 12 PRO MAX', 'Iphone 12 PRO MAX sin cargador', 399.00, 3, 0.00, 'iphone 12 pro max.jpg', 1),
(15, 'IPHONE 11', 'Iphone 11 con cargador reacondicionado', 170.00, 3, 0.00, 'iphone 11.jpg', 1),
(16, 'PS2', 'Consola PS2 con una tarjeta de memoria y 1 mando', 99.00, 4, 0.00, 'ps2.webp', 3),
(17, 'XBOX SERIES X', 'Consola XBOX SERIES X nueva con 2 mandos y COD BO6', 450.00, 4, 0.00, 'xbox-seriesx.jpg', 6),
(19, 'XBOX ONE', 'Consola XBOX ONE blanca con 4 juegos', 200.00, 4, 0.00, 'one-xbox.jpg', 5),
(20, 'IPHONE 16', 'Nuevo IPHONE 16 sin cargador ', 1200.00, 3, 0.00, 'iphone-16.jpg', 20),
(21, 'IPHONE XS MAX', 'IPHONE XS MAX reacondicionado con caja y cargador oridinales', 180.00, 3, 0.00, 'xs-max.jpg', 3),
(22, 'MANDO PS4 ORIGINAL', 'Mando compatible con PS4 nuevo a estrenar', 47.00, 4, 0.00, 'mandops4.jpg', 3),
(25, 'MANDO PS5 ORIGINAL', 'Mando PS5 ORIGINAL nuevo a estrenar', 75.00, 4, 0.00, 'MANDO-PS5.png', 4),
(26, 'MANDO XBOX ONE ORIGINAL', 'Mando XBOX ONE ORIGINAL nuevo a estrenar', 69.00, 4, 0.00, 'mandoxbox.avif', 3),
(28, 'MANDO XBOX SERIES X ORIGINAL', 'Mando XBOX SERIES X ORIGINAL nuevo a estrenar', 80.00, 4, 0.00, 'mando-series-x2.png', 6),
(30, 'CARGDOR IPHONE 14 ORIGINAL', 'Cargador para Iphone 11/12/13/14 compatible con todas sus versiones y ORIGINAL', 55.00, 3, 0.00, 'cargador-iphone.jpg', 7),
(31, 'GeForce RTX 4080 SUPER WINDFORCE V2 16GB GDDR6X DLSS3', 'Las GPU NVIDIA® GeForce RTX® serie 40 son más que rápidas para jugadores y creadores. Cuentan con la tecnología de la arquitectura ultra eficiente NVIDIA Ada Lovelace, que ofrece un salto espectacular tanto en rendimiento como en gráficos con tecnología de IA. Disfruta de mundos virtuales realistas con trazado de rayos y juegos con FPS ultra altos y la latencia más baja. Descubre nuevas y revolucionarias formas de crear contenido y una aceleración de flujo de trabajo sin precedentes.', 1049.00, 15, 0.00, 'rtx-4080-super.webp', 3),
(32, 'MSI GeForce RTX 4070 VENTUS 2X E OC 12GB GDDR6X DLSS3', 'Las GPU NVIDIA® GeForce RTX® serie 40 son más que rápidas para jugadores y creadores. Cuentan con la tecnología de la arquitectura ultra eficiente NVIDIA Ada Lovelace, que ofrece un salto espectacular tanto en rendimiento como en gráficos con tecnología de IA. Disfruta de mundos virtuales realistas con trazado de rayos y juegos con FPS ultra altos y la latencia más baja. Descubre nuevas y revolucionarias formas de crear contenido y una aceleración de flujo de trabajo sin precedentes.', 569.00, 15, 0.00, 'MSI-rtx-4070.webp', 2),
(33, 'GeForce RTX­­ 4060 GAMING OC 8GB GDDR6 DLSS3', 'Las GPU NVIDIA® GeForce RTX® serie 40 son más que rápidas para jugadores y creadores. Cuentan con la tecnología de la arquitectura ultra eficiente NVIDIA Ada Lovelace, que ofrece un salto espectacular tanto en rendimiento como en gráficos con tecnología de IA. Disfruta de mundos virtuales realistas con trazado de rayos y juegos con FPS ultra altos y la latencia más baja. Descubre nuevas y revolucionarias formas de crear contenido y una aceleración de flujo de trabajo sin precedentes.', 319.00, 15, 0.00, 'rtx-4060.webp', 4),
(34, 'MSI MPG B550 GAMING PLUS ', 'La serie MPG saca lo mejor de los jugadores al permitir una expresión completa en color con control avanzado de iluminación RGB y sincronización. Experimente en otro nivel de personalización con una tira de LED frontal que proporciona notificaciones convenientes en el juego y en tiempo real. Con la serie MPG, transforme su equipo en el centro de atención y las mejores tablas de clasificación con estilo.\r\n\r\n', 132.00, 15, 0.00, 'placa-msi-b550.webp', 1),
(35, 'Placa Base MSI B760M PROJECT ZERO WIFI', 'El B760M PROJECT ZERO presenta un diseño único de conexión posterior que reduce la complejidad de la gestión de cables, lo que da como resultado una apariencia más ordenada y estéticamente más agradable para el sistema. Es compatible con los procesadores Intel 14.º/13.º/12.º, admite DDR5 y viene equipado con una solución LAN 2.5G y Wi-Fi 6E. Esto la convierte en una excelente opción entre las placas base mATX.', 251.00, 15, 0.00, 'placa-msi-b760m.webp', 7),
(36, 'Placa Base ASUS ROG CROSSHAIR X870E HERO', 'Placa base ASUS ROG CROSSHAIR X870E HERO PC-ready con IA avanzada, 18+2+2 fases de poder, Dynamic OC Switcher, Core Flex, ranuras DDR5 con AEMP y Tecnología DRAM NitroPath, Wi-Fi 7 con ASUS WiFi Q-Antenna, cinco puertos M.2 integrados, tres puertos SSD PCIe® 5.0 NVMe® integrados, conector SlimSAS, PCIe® 5.0 x16 SafeSlots con PCIe® Q-Release Slim, dos puertos USB4®, dos puertos USB 20Gbps Type-C® en el panel frontal (uno con Quick Charge 4+ hasta 60 W y USB Wattage Watcher), AI Overclocking, AI Cooling II, AI Networking II y Polymo Lighting II.\r\n\r\nDiseñada para el futuro de la computación con IA, con la potencia y la conectividad necesarias para aplicaciones de IA exigentes. Preparada para procesadores AMD Ryzen™ 9000 y 8000 y procesadores de escritorio de la serie 7000.\r\n\r\nDos puertos USB4® Type-C®, dos puertos USB 20Gbps Type-C® en el panel frontal (uno con Quick Charge 4+ de hasta 60 W y USB Wattage Watcher), ocho puertos USB 10Gbps adicionales, conector SlimSAS compatible con PCIe® 4.0 x4, dos SafeSlots PCIe® 5.0 x16, puerto HDMI™.', 639.00, 15, 0.00, 'placa-asus-rog-x870e.webp', 3),
(37, 'Placa Base ASUS ROG STRIX Z890-A GAMING WIFI', 'Con Thunderbolt™ 4, PCIe ® 5.0 e iluminación Polymo, la ROG Strix Z890-A ofrece transferencias de datos ultrarrápidas, una conectividad excepcional y una estética personalizable que puede ser sutil o llamativa. Esta placa base ofrece la potencia y la velocidad esenciales para los juegos de élite y las demandas de las aplicaciones avanzadas de PC con IA.', 439.00, 15, 0.00, 'placa-asus-rog-z890A.webp', 4),
(38, 'ASUS ROG STRIX X670E-E GAMING WIFI', 'El WiFi para juegos ROG Strix X670E-E se encuentra en la parte superior de la serie, con una entrega de energía robusta y herramientas exclusivas de overclocking ROG para maximizar las estadísticas de los procesadores AMD Ryzen ™ 7000 Series. La compatibilidad con PCIe 5.0 se extiende hasta los confines de las capacidades del conjunto de chips, y se prepara un disipador térmico adicional para mantener refrigeradas incluso las unidades M.2 más rápidas. Con un rendimiento líder, la última conectividad y funciones fáciles de usar que abarcan desde la instalación del hardware hasta la configuración del sistema, esta bestia Strix se agarra a AM5 y se eleva sin esfuerzo hacia la victoria.', 483.00, 15, 0.00, 'placa-asus-rog-x670e.webp', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_estrella`
--

CREATE TABLE `productos_estrella` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `fecha_destacado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos_estrella`
--

INSERT INTO `productos_estrella` (`id`, `producto_id`, `fecha_destacado`) VALUES
(4, 26, '2024-12-02 23:46:32'),
(15, 22, '2024-12-03 00:06:06'),
(17, 21, '2024-12-03 00:06:19'),
(20, 13, '2024-12-10 20:51:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(50) NOT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tipo` enum('normal','administrador') DEFAULT 'normal',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellidos`, `direccion`, `telefono`, `email`, `password`, `tipo`, `fecha_registro`, `foto`) VALUES
(2, 'Gabriel Aracil Pizana', 'Aracil Pizana', 'Calle ramon gallud 76', '605936706', 'gabriel946742@gmail.com', '$2y$10$kAXmO8a7g7mLhN1g/G6TEOqTNwCCrKWXanwgLaWlQRFFULNtFkZH.', 'administrador', '2024-11-11 01:02:16', 'MINIATURA ALAKAZAM EX 151.png'),
(3, 'Pepe', 'Aracil Pizana', 'Calle ramon gallud 76', '649633369', 'gabriel9467421@gmail.com', '$2y$10$tE/Nn0x2ht5tr7iR95B82u8rxCqLivERXPOUBrkR.G2z9unhgah66', 'normal', '2024-11-12 00:49:17', 'placa-msi-b760m.webp'),
(4, 'Antonio', 'Perez Lopez', 'Calle ramon gallud 76', '690367504', 'gabriel946742121@gmail.com', '$2y$10$cfMQ8SZQ8FaSpowpYOnr7Onf5qn.fbHsjDVlO.9sk5qt/b2eI9gfK', 'normal', '2024-11-13 15:19:38', 'escudo_perfil.png'),
(10, 'ORDENADORESS', 'Aracil Pizana', 'Calle ramon gallud 76', '689045679', 'gabriel94674222@gmail.com', '$2y$10$0FH4vHdYN7yHND.nP1eqfO0I2sEwTccJy9Tye3TdbZ0E.H4nng4Ym', 'normal', '2024-11-26 00:18:44', 'iphone 15.jpg'),
(11, 'ORDENADORESS', 'Perez Lopez', 'Calle ramon gallud 76', '657890567', 'gabriel946742222@gmail.com', '$2y$10$zMxvqdontNxk/Gukb53ODOTBBxLuilY2bA3GYAmc6Ws52eeQRfj1q', 'normal', '2024-11-26 00:20:04', 'iphone-16.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `valoraciones`
--

CREATE TABLE `valoraciones` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `valor` int(11) DEFAULT NULL CHECK (`valor` between 1 and 5),
  `fecha_valoracion` datetime DEFAULT current_timestamp(),
  `pedido_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `valoraciones`
--

INSERT INTO `valoraciones` (`id`, `producto_id`, `usuario_id`, `valor`, `fecha_valoracion`, `pedido_id`) VALUES
(5, 4, 2, 5, '2024-12-03 22:48:25', 0),
(6, 9, 2, 5, '2024-12-03 23:05:58', 0),
(7, 16, 2, 5, '2024-12-03 23:08:43', 0),
(8, 11, 2, 5, '2024-12-03 23:09:02', 0),
(11, 9, 3, 3, '2024-12-04 00:28:03', 0),
(18, 20, 2, 5, '2024-12-12 02:46:59', 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categorias_ibfk_1` (`categoria_padre`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `productos_estrella`
--
ALTER TABLE `productos_estrella`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `productos_estrella`
--
ALTER TABLE `productos_estrella`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`categoria_padre`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `productos_estrella`
--
ALTER TABLE `productos_estrella`
  ADD CONSTRAINT `productos_estrella_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  ADD CONSTRAINT `valoraciones_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `valoraciones_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
