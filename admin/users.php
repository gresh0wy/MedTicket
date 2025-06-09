<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

// Pobierz uprawnienia użytkownika
$user_id = $_SESSION['user_id'];
$user_permissions = [];

$permissions_sql = "SELECT p.name FROM user_permissions up
                    JOIN permissions p ON up.permission_id = p.id
                    WHERE up.user_id = ?";
$stmt = $conn->prepare($permissions_sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $user_permissions[] = $row['name'];
}

// Sprawdź, czy użytkownik ma odpowiednie uprawnienia
if (!in_array('Super Administrator', $user_permissions) && !in_array('Administrator', $user_permissions)) {
    header("Location: ../login.php");
    exit();
}

// Pobierz wartość wyszukiwania, jeśli istnieje
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pobierz wszystkich użytkowników z filtrowaniem
$sql = "SELECT id, username, first_name, last_name, blocked FROM users WHERE username LIKE ? OR first_name LIKE ? OR last_name LIKE ?";
$stmt = $conn->prepare($sql);
$search_param = '%' . $search . '%';
$stmt->bind_param("sss", $search_param, $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();

if ($conn->error) {
    echo "Błąd: " . $conn->error;
    exit();
}

// Obsługa tworzenia nowego konta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Sprawdź, czy nazwa użytkownika już istnieje
    $check_user_sql = "SELECT id FROM users WHERE username = ?";
    $check_user_stmt = $conn->prepare($check_user_sql);
    $check_user_stmt->bind_param("s", $username);
    $check_user_stmt->execute();
    $check_user_result = $check_user_stmt->get_result();

    if ($check_user_result->num_rows > 0) {
        $error_message = "Nazwa użytkownika jest już zajęta.";
    } else {
        // Wstawienie danych do bazy
        $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $first_name, $last_name);

        if ($stmt->execute()) {
            $success_message = "Konto zostało utworzone pomyślnie.";
        } else {
            $error_message = "Błąd: " . $stmt->error;
        }

        $stmt->close();
    }
    $check_user_stmt->close();
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Zarządzania Użytkownikami - MedTicket</title>
    <link rel="stylesheet" href="../assets/css/UsersPage.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            outline: none;
            color: #fff;
            background-color:  #4a90e2;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            margin-bottom: 20px;
        }

        .button:hover {
            background-color:  #357abd;
        }

        /* Style dla modala */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .modal-button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .modal-button:hover {
            background-color: #45a049;
        }

        .search-form {
            margin-bottom: 20px;
        }

        .search-container {
            display: flex;
            align-items: center;
        }

        .search-container input[type="text"] {
            margin-right: 10px;
        }

        .search-container button {
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            color: #fff;
            background-color: #4a90e2;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .search-container button:hover {
            background-color: #357abd;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleDropdown = (event, dropdown) => {
                const openDropdowns = document.querySelectorAll('.dropdown-content');
                openDropdowns.forEach(d => {
                    if (d !== dropdown) {
                        d.style.display = 'none';
                    }
                });
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            };

            

            window.addEventListener('click', (event) => {
                const openDropdowns = document.querySelectorAll('.dropdown-content');
                openDropdowns.forEach(d => {
                    if (!d.contains(event.target)) {
                        d.style.display = 'none';
                    }
                });
            });

            // Otwieranie modala
            document.getElementById('addUserBtn').addEventListener('click', function() {
                document.getElementById('addUserModal').style.display = 'block';
            });

            // Zamknięcie modala
            document.getElementById('closeModal').addEventListener('click', function() {
                document.getElementById('addUserModal').style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target == document.getElementById('addUserModal')) {
                    document.getElementById('addUserModal').style.display = 'none';
                }
            });
        });
    </script>
</head>
<body>
    <header>
        <h1>Panel Zarządzania Użytkownikami</h1>
    </header>
    <?php include '../admin/includes/navigation.php'; ?>
    <div class="container">
        <h2>Lista użytkowników</h2>

        <!-- Formularz wyszukiwania -->
        <form method="GET" action="users.php" class="search-form">
            <div class="search-container">
                <input type="text" name="search" placeholder="Szukaj użytkowników..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit">Szukaj</button>
            </div>
        </form>

        <!-- Przyciski -->
        <button id="addUserBtn" class="button">Dodaj użytkownika</button>

        <table>
            <tr>
                <th>Login</th>
                <th>Imię</th>
                <th>Nazwisko</th>
                <th>Uprawnienia</th>
                <th>Akcje</th>
            </tr>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php
                    $user_id = $row['id'];
                    $permissions_sql = "SELECT name FROM permissions
                                        JOIN user_permissions ON permissions.id = user_permissions.permission_id
                                        WHERE user_permissions.user_id = ?";
                    $permissions_stmt = $conn->prepare($permissions_sql);
                    $permissions_stmt->bind_param('i', $user_id);
                    $permissions_stmt->execute();
                    $permissions_result = $permissions_stmt->get_result();
                    $permissions = [];
                    while ($permission_row = $permissions_result->fetch_assoc()) {
                        $permissions[] = $permission_row['name'];
                    }
                    $blocked = $row['blocked'];
                    ?>
                    <tr style="color: <?php echo $blocked ? 'red' : 'black'; ?>;">
                        <td><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(implode(', ', $permissions), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <form action="user_details.php" method="get">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="details-button">Szczegóły</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Brak użytkowników</td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- Okno modalne -->
        <div id="addUserModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeModal">&times;</span>
                <h2>Dodaj nowego użytkownika</h2>
                <form method="POST" action="users.php">
                    <div class="form-group">
                        <label>Imię:</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Nazwisko:</label>
                        <input type="text" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Nazwa użytkownika:</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Hasło:</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" name="create_user" class="modal-button">Utwórz konto</button>
                </form>
                <?php if (isset($error_message)): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <?php if (isset($success_message)): ?>
                    <p style="color: green;"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php $conn->close(); ?>
    <script src="../assets/js/navigation.js"></script>

</body>
</html>
