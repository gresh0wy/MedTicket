<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

// Sprawdź uprawnienia użytkownika
$permissions = $_SESSION['permissions'] ?? [];

if (!in_array('Super Administrator', $permissions) && !in_array('Administrator', $permissions)) {
    echo json_encode(['success' => false, 'message' => 'Brak uprawnień']);
    exit();
}

// Odbierz dane z formularza
$user_id = $_POST['user_id'] ?? null;
$username = $_POST['username'] ?? null;
$first_name = $_POST['first_name'] ?? null;
$last_name = $_POST['last_name'] ?? null;

if (!$user_id || !$username || !$first_name || !$last_name) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe dane']);
    exit();
}

// Zaktualizuj dane użytkownika
$sql = "UPDATE users SET username = ?, first_name = ?, last_name = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $username, $first_name, $last_name, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Błąd podczas aktualizacji danych użytkownika']);
}

$stmt->close();
$conn->close();
?>
