<?php
// Conexion Server
define("MYSQL_HOST", "mysql:host=localhost");
define("MYSQL_USER", "root");
define("MYSQL_PASSWORD", "");
define('MYSQL_DB', 'mi_tienda_online');

function conectaDB($db = 'mi_tienda_online')
{
    try {
        $tmp = new PDO(MYSQL_HOST . ";dbname=" . $db, MYSQL_USER, MYSQL_PASSWORD);
        $tmp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $tmp->exec("set names utf8mb4");
        return $tmp;
    } catch (PDOException $e) {
        print "<p>Error: No puede conectarse con la base de datos.</p>\n";
        print "<p>Error: " . $e->getMessage() . "</p>\n";
        exit();
    }
}
?>
