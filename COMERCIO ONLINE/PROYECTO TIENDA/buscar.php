<?php
if (isset($_GET['query'])) {
    // Obtener el término de búsqueda
    $busqueda = trim($_GET['query']);

    // Redirigir a productos.php con el término de búsqueda como parámetro
    header("Location: productos.php?query=" . urlencode($busqueda));
    exit();
} else {
    // Si no hay término de búsqueda, redirigir a productos.php sin filtros
    header("Location: productos.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Buscar - GAP COMPONENTS</title>
    <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Resultados para: <?= htmlspecialchars($busqueda) ?></h1>
    <?php if (!empty($resultados)): ?>
        <ul>
            <?php foreach ($resultados as $producto): ?>
                <li>
                    <a href="detalles.php?id=<?= $producto['id'] ?>">
                        <?= htmlspecialchars($producto['nombre']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No se encontraron resultados.</p>
    <?php endif; ?>
</body>
</html>
