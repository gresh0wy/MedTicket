<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../'); // wskazuje na katalog projektu MedTicket
$dotenv->load();

// Teraz możesz odczytywać zmienne środowiskowe przez $_ENV lub getenv()
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];
$port = $_ENV['DB_PORT'] ?? 3306; // jeśli nie ustawiony, domyślnie 3306

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


// Ustawienie kodowania (bardzo ważne dla polskich znaków)
$conn->set_charset("utf8mb4");
