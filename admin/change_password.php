<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['password'];

    // Walidacja hasła: minimum 8 znaków, co najmniej jedna wielka litera, mała litera, cyfra i znak specjalny
    if (preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/", $new_password)) {
        // Szyfrowanie hasła za pomocą bcrypt z kosztem 12
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

        // Zaktualizowanie hasła w bazie danych
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Hasło zostało zmienione.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Błąd podczas zmiany hasła.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Hasło nie spełnia wymagań.']);
    }
}

$conn->close();

?>
