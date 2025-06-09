<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Super Administrator', 'Administrator'])) {
    echo json_encode(['success' => false, 'message' => 'Brak dostępu']);
    exit();
}

include '../includes/db.php';

if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Brakujący identyfikator użytkownika']);
    exit();
}

$user_id = $_GET['user_id'];

$user_permissions_sql = "SELECT p.id, p.name FROM user_permissions up JOIN permissions p ON up.permission_id = p.id WHERE up.user_id = ?";
$stmt = $conn->prepare($user_permissions_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$permissions = [];
while ($row = $result->fetch_assoc()) {
    $permissions[] = $row;
}

echo json_encode(['success' => true, 'permissions' => $permissions]);

$stmt->close();
$conn->close();
?>
