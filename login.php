<?php
session_start();

include 'includes/db.php';

if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $https_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $https_url);
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id, role, password, first_name, last_name, blocked FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['blocked']) {
            $error_message = "Konto zostało zablokowane.";
        } else if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];

            // Pobieranie uprawnień użytkownika
            $user_id = $user['id'];
            $query = "SELECT p.name FROM user_permissions up
                      JOIN permissions p ON up.permission_id = p.id
                      WHERE up.user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $user_permissions = [];
            while ($row = $result->fetch_assoc()) {
                $user_permissions[] = $row['name'];
            }

            // Zapisywanie uprawnień w sesji
            $_SESSION['permissions'] = $user_permissions;

            header("Location: index.php");
            exit();
        } else {
            $error_message = "Błędne hasło lub login.";
        }
    } else {
        $error_message = "Błędne hasło lub login.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie - MedTicket</title>
    <link rel="stylesheet" href="assets/css/Login.css">

</head>
<body>
    
  <!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <div class="grid_container">
        <div class="grid_item item1">
            <h1 class="logo_heading">
                <img src="./assets/Icons/logo/monitor-color2.png" alt="Logo" class="logo_icon">
                MedTicket
            </h1>


            <h2>Witaj ponownie!</h2>
            <form action="login.php" method="post" class="login_form">
                <div class="form_group">
                    <label for="username">Nazwa użytkownika</label>
                    <input type="text" id="username" name="username" placeholder="Wpisz nazwę użytkownika" required
                        autofocus>
                </div>
                <div class="form_group">
                    <label for="password">Hasło</label>
                    <input type="password" id="password" name="password" placeholder="Wpisz hasło" required>
                </div>
                <button class="btn_submit" type="submit">Zaloguj się</button>
                 <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
            </form>
        </div>
        <div class="grid_item item2">
              <img src="./assets/img/logo-szpital.png" alt="Logo" class="logo_zeroms">
        </div>
    </div>
    </div>
</body>

</html>
    <script src="assets/js/navigation.js"></script>
</body>
</html>
 
 

