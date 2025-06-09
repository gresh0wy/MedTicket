<?php
require '../vendor/autoload.php'; // Załaduj bibliotekę JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sprawdzenie, czy użytkownik jest zalogowany poprzez sesję lub JWT
if (!isset($_SESSION['jwt'])) {
    header("Location: ../login.php");
    exit();
}

$key = "your_secret_key"; // Twój klucz prywatny
$jwt = $_SESSION['jwt'];

try {
    // Weryfikacja tokenu JWT
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    
    // Pobranie danych użytkownika z tokenu
    $user_id = $decoded->user_id;
    $user_permissions = $decoded->permissions;

    // Przechowanie danych w sesji (opcjonalne, dla łatwiejszego dostępu)
    $_SESSION['user_id'] = $user_id;
    $_SESSION['permissions'] = $user_permissions;

} catch (Exception $e) {
    // Jeśli token jest nieprawidłowy lub wygasł
    echo "Błąd: Nieprawidłowy token lub token wygasł.";
    exit();
}

// Funkcja do sprawdzania uprawnień
function check_permissions($required_permissions = []) {
    global $user_permissions;

    // Sprawdzenie, czy użytkownik posiada jedno z wymaganych uprawnień
    foreach ($required_permissions as $required_permission) {
        if (in_array($required_permission, $user_permissions)) {
            return true;  // Ma odpowiednie uprawnienie
        }
    }

    // Jeżeli użytkownik nie ma żadnego z wymaganych uprawnień
    $missing_permissions = implode(", ", $required_permissions);
    echo "<h2>Brak wymaganych uprawnień: {$missing_permissions}</h2>";
    
    // Możesz opcjonalnie przenieść użytkownika na stronę błędu lub stronę główną
    // header("Location: ../no_access.php?missing_permissions=" . urlencode($missing_permissions));
    
    exit();  // Zatrzymanie dalszego działania skryptu
}
