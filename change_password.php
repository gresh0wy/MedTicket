<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password']; // Powtórzone hasło
    $user_id = $_SESSION['user_id'];

    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Hasła nie są zgodne.']);
        exit();
    }

    // Pobierz aktualne hasło z bazy danych
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($password_hash);
    $stmt->fetch();
    $stmt->close();

    // Sprawdź, czy obecne hasło jest poprawne
    if (password_verify($current_password, $password_hash)) {
        // Zaktualizuj hasło w bazie danych
        $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_password_hash, $user_id);

        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Hasło zostało pomyślnie zmienione.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Błąd podczas zmiany hasła: ' . $update_stmt->error]);
        }

        $update_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Obecne hasło jest niepoprawne.']);
    }

    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zmień hasło - MedTicket</title>
    <link rel="stylesheet" href="assets/css/MainPageStyle.css">
    <style>
        .invalid {
            color: red;
        }
        .valid {
            color: green;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background: #fff;
            padding: 20px;
            margin: 15% auto;
            width: 30%;
            text-align: center;
        }
        .modal-content .btn {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>MedTicket</h1>
    </header>
    <?php include 'includes/navigation.php'; ?>
    <div class="container">
        <h2>Zmień hasło</h2>
        <form id="changePasswordForm" method="post">
            <label for="current_password">Obecne hasło:</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">Nowe hasło:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Powtórz nowe hasło:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <!-- Lista wymagań hasła -->
            <ul id="passwordRequirements" style="list-style-type: none; padding: 0;">
                <li id="requireLength" class="invalid">Minimum 8 znaków</li>
                <li id="requireUpperCase" class="invalid">Co najmniej jedna wielka litera</li>
                <li id="requireLowerCase" class="invalid">Co najmniej jedna mała litera</li>
                <li id="requireNumber" class="invalid">Co najmniej jedna cyfra</li>
                <li id="requireSpecialChar" class="invalid">Co najmniej jeden znak specjalny</li>
                <li id="requireMatch" class="invalid">Hasła muszą się zgadzać</li>
            </ul>

            <button type="submit">Zmień hasło</button>
        </form>
    </div>

    <!-- Modal do wyświetlania statusu zmiany hasła -->
    <div id="statusChangePasswordModal" class="modal">
        <div class="modal-content">
            <h3 id="statusChangePasswordTitle"></h3>
            <p id="statusChangePasswordText"></p>
            <button type="button" class="btn" id="closeModalButton">Zamknij</button>
        </div>
    </div>

    <script>
        // Walidacja hasła w czasie rzeczywistym
        document.getElementById('new_password').addEventListener('input', validatePassword);
        document.getElementById('confirm_password').addEventListener('input', validatePassword);

        function validatePassword() {
            var password = document.getElementById('new_password').value;
            var confirmPassword = document.getElementById('confirm_password').value;

            var lengthRequirement = /.{8,}/;
            var upperCaseRequirement = /[A-Z]/;
            var lowerCaseRequirement = /[a-z]/;
            var numberRequirement = /\d/;
            var specialCharRequirement = /[\W_]/;

            updateRequirementStatus('requireLength', lengthRequirement.test(password));
            updateRequirementStatus('requireUpperCase', upperCaseRequirement.test(password));
            updateRequirementStatus('requireLowerCase', lowerCaseRequirement.test(password));
            updateRequirementStatus('requireNumber', numberRequirement.test(password));
            updateRequirementStatus('requireSpecialChar', specialCharRequirement.test(password));
            updateRequirementStatus('requireMatch', password === confirmPassword);
        }

        function updateRequirementStatus(elementId, isValid) {
            var element = document.getElementById(elementId);
            if (isValid) {
                element.classList.remove('invalid');
                element.classList.add('valid');
            } else {
                element.classList.remove('valid');
                element.classList.add('invalid');
            }
        }

        document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Zatrzymanie domyślnej akcji formularza
            var formData = new FormData(this);

            fetch('change_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showModal(data.success, data.message);
            })
            .catch(error => {
                showModal(false, 'Wystąpił błąd podczas zmiany hasła.');
            });
        });

        function showModal(success, message) {
            var modal = document.getElementById('statusChangePasswordModal');
            var title = document.getElementById('statusChangePasswordTitle');
            var text = document.getElementById('statusChangePasswordText');

            title.innerText = success ? 'Sukces' : 'Błąd';
            text.innerText = message;
            title.style.color = success ? 'green' : 'red';

            modal.style.display = 'block';

            document.getElementById('closeModalButton').onclick = function() {
                modal.style.display = 'none';
            };
        }
    </script>
        <script src="assets/js/navigation.js"></script>

</body>
</html>
