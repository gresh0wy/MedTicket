<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

// Sprawdź uprawnienia użytkownika
$permissions = $_SESSION['permissions'] ?? [];

if (!in_array('Super Administrator', $permissions)) {
    echo json_encode(['success' => false, 'message' => 'Brak uprawnień']);
    exit();
}

// Odbierz dane z formularza
$user_id = $_POST['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe żądanie']);
    exit();
}

// Usuń użytkownika
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    // Przekierowanie lub informacja o sukcesie
    header("Location: users.php?deleted=1"); // lub inna strona
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Błąd podczas usuwania użytkownika']);
}

$stmt->close();
$conn->close();
?>
