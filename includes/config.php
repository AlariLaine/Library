<?php
define('ROOT_PATH', dirname(__DIR__));

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'raamatukogu');

session_start();

try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER, 
        DB_PASS
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES 'utf8'");
    $conn->exec("SET CHARACTER SET utf8");
    
} catch(PDOException $e) {
    die("Andmebaasiga ühendumine ebaõnnestus: " . $e->getMessage());
}

require_once 'functions.php';
require_once 'auth.php';
?>