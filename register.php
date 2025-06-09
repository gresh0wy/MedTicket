<?php
include 'includes/db.php'; // Połączenie z bazą danych

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Wstawienie danych do bazy
    $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hashed_password, $first_name, $last_name);

    if ($stmt->execute()) {
        echo "Konto zostało utworzone pomyślnie.";
    } else {
        echo "Błąd: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja</title>
    <link rel="stylesheet" href="assets/css/Register.css">
</head>
<body>
    <h2>Rejestracja</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label>Imię:</label>
        <input type="text" name="first_name" required><br><br>
        <label>Nazwisko:</label>
        <input type="text" name="last_name" required><br><br>
        <label>Nazwa użytkownika:</label>
        <input type="text" name="username" required><br><br>
        <label>Hasło:</label>
        <input type="password" name="password" required><br><br>
        <button type="submit">Zarejestruj się</button>
    </form>
</body>
</html>
