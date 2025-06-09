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

// Odbierz dane JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['block'])) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe żądanie']);
    exit();
}

$user_id = $data['user_id'];
$block = $data['block'] ? 1 : 0;

// Zaktualizuj status blokady użytkownika
$sql = "UPDATE users SET blocked = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $block, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Błąd podczas aktualizacji statusu użytkownika']);
}

$stmt->close();
$conn->close();
?>
