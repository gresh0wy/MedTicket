<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Brak uprawnień']);
    exit();
}

include '../includes/db.php';

// Sprawdź, czy użytkownik ma uprawnienie "Super Administrator"
$user_id = $_SESSION['user_id'];
$required_permission = 'Super Administrator';  // Uprawnienie, które ma być sprawdzone

$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM user_permissions up 
    JOIN permissions p ON up.permission_id = p.id 
    WHERE up.user_id = ? AND p.name = ?
");
$stmt->bind_param("is", $user_id, $required_permission);
$stmt->execute();
$stmt->bind_result($permission_count);
$stmt->fetch();
$stmt->close();

if ($permission_count == 0) {
    echo json_encode(['success' => false, 'message' => 'Brak odpowiednich uprawnień']);
    exit();
}

// Get the posted data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !isset($data['permissions'])) {
    echo json_encode(['success' => false, 'message' => 'Błędne dane']);
    exit();
}

$user_id_to_update = $data['user_id'];
$permissions = $data['permissions'];

$conn->begin_transaction();

try {
    // Delete existing permissions
    $stmt = $conn->prepare("DELETE FROM user_permissions WHERE user_id = ?");
    $stmt->bind_param("i", $user_id_to_update);
    $stmt->execute();
    $stmt->close();

    // Insert new permissions
    $stmt = $conn->prepare("INSERT INTO user_permissions (user_id, permission_id) VALUES (?, ?)");
    foreach ($permissions as $permission_id) {
        $stmt->bind_param("ii", $user_id_to_update, $permission_id);
        $stmt->execute();
    }
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Wystąpił błąd podczas aktualizacji uprawnień.']);
}

$conn->close();

?>

