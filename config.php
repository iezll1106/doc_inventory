<?php
$host = '127.0.0.1';
$port = 3307;
$db   = 'doc_inventory';  // replace with your database name
$user = 'root';
$pass = '';  // if you pressed Enter when logging in, leave empty. Otherwise put your password here.

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}
