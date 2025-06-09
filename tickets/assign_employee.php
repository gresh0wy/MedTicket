<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticket_id = $_POST['ticket_id'];
    $employee_id = $_POST['employee_id'];

    // Przygotowanie zapytania SQL do aktualizacji zgłoszenia
    $stmt = $conn->prepare("UPDATE tickets SET employee_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $employee_id, $ticket_id);

    if ($stmt->execute()) {
        // Zwrócenie odpowiedzi "success" dla AJAX
        echo "success";
    } else {
        // Zwrócenie błędu dla AJAX
        echo "error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "error: unauthorized access";
}
?>
