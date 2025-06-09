<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

// Pobierz uprawnienia użytkownika z sesji
$permissions = $_SESSION['permissions'] ?? [];

// Sprawdź, czy użytkownik ma odpowiednie uprawnienia
if (!in_array('Super Administrator', $permissions) && !in_array('Administrator', $permissions)) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// Pobierz szczegóły użytkownika
$sql = "SELECT id, first_name, last_name, username, blocked FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: users.php");
    exit();
}

$user = $result->fetch_assoc();

// Pobierz uprawnienia użytkownika
$permissions_sql = "SELECT id, name FROM permissions";
$permissions_result = $conn->query($permissions_sql);

$user_permissions_sql = "SELECT permission_id FROM user_permissions WHERE user_id = ?";
$stmt = $conn->prepare($user_permissions_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_permissions_result = $stmt->get_result();
$user_permissions = [];
while ($row = $user_permissions_result->fetch_assoc()) {
    $user_permissions[] = $row['permission_id'];
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szczegóły użytkownika - MedTicket</title>
    <link rel="stylesheet" href="../assets/css/user_details.css">
   <style>/* Stylizacja dla niespełnionych wymagań */
.invalid {
    color: red;
}

/* Stylizacja dla spełnionych wymagań */
.valid {
    color: green;
}

</style>
</head>
<body>
    <header>
        <h1>Szczegóły użytkownika</h1>
    </header>
    <?php include '../admin/includes/navigation.php'; ?>

    <div class="container">
        <h2><?php echo htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
        
        <?php if (in_array('Super Administrator', $permissions)): ?>
            <div class="permissions-section">
                <h3>Aktywne Uprawnienia</h3>
                <div id="activePermissionsList" class="checkbox-group">
                    <?php 
                    if ($permissions_result) {
                        while ($row = $permissions_result->fetch_assoc()): ?>
                            <?php if (in_array($row['id'], $user_permissions)): ?>
                                <div class="checkbox-container">
                                    <span class="checkbox-label"><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endwhile; 
                    } else {
                        echo "<p>Brak dostępnych uprawnień.</p>";
                    }
                    ?>
                </div>
                <button class="btn" id="permissionsButton">Zarządzaj Uprawnieniami</button>
            </div>

            <div class="delete-password-container">
                <div class="delete-section">
                    <h3>Usuń użytkownika</h3>
                    <button class="btn btn-danger" id="deleteButton">Usuń</button>
                </div>
                <div class="password-section">
                    <h3>Zmień hasło</h3>
                    <button class="btn" id="changePasswordButton">Zmień hasło</button>
                </div>
                <div class="edit-details-container">
                    <h3>Edytuj Dane</h3>
                    <button class="btn" id="editDetailsButton">Edytuj Dane</button>
                </div>
                <div class="centered-block-user-container block-user-container centered-block-user-container">
                    <h3>Blokuj Konto</h3>
                    <button class="btn btn-warning" id="blockUserButton" style="<?php echo $user['blocked'] ? 'display:none;' : ''; ?>">Blokuj</button>
                    <button class="btn btn-success" id="unblockUserButton" style="<?php echo $user['blocked'] ? '' : 'display:none;'; ?>">Odblokuj</button>
                </div>
            </div>
        <?php elseif (in_array('Administrator', $permissions)): ?>
            <div class="password-section">
                <h3>Zmień hasło</h3>
                <button class="btn" id="changePasswordButton">Zmień hasło</button>
            </div>
            <div class="edit-details-container">
                <h3>Edytuj Dane</h3>
                <button class="btn" id="editDetailsButton">Edytuj Dane</button>
            </div>
        <?php else: ?>
            <p>Brak uprawnień do zarządzania użytkownikiem.</p>
        <?php endif; ?>
    </div>

    <!-- Permissions Modal -->
    <div id="permissionsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Zarządzaj Uprawnieniami</h3>
            <div class="permissions-container">
                <div class="permissions-column">
                    <h4>Dostępne Uprawnienia</h4>
                    <div id="availablePermissions" class="permissions-list">
                        <?php 
                        $permissions_result->data_seek(0); // reset result pointer
                        while ($row = $permissions_result->fetch_assoc()): 
                            if (!in_array($row['id'], $user_permissions)): ?>
                            <div class="permission-item" data-id="<?php echo $row['id']; ?>">
                                <?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="permissions-column">
                    <h4>Aktywne Uprawnienia</h4>
                    <div id="modalActivePermissions" class="permissions-list">
                        <?php 
                        $permissions_result->data_seek(0); // reset result pointer
                        while ($row = $permissions_result->fetch_assoc()): 
                            if (in_array($row['id'], $user_permissions)): ?>
                            <div class="permission-item" data-id="<?php echo $row['id']; ?>">
                                <?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <button type="button" class="btn" id="savePermissionsButton">Zapisz</button>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" id="deleteClose">&times;</span>
            <h3>Usuń użytkownika</h3>
            <p>Czy na pewno chcesz usunąć tego użytkownika?</p>
            <form action="delete_user.php" method="post" class="form">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-danger">Usuń</button>
            </form>
        </div>
    </div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="modal">
    <div class="modal-content">
        <span class="close" id="changePasswordClose">&times;</span>
        <h3>Zmień hasło</h3>
        <form action="change_password.php" method="post" class="form" id="changePasswordForm">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>">
            
            <label for="password">Nowe hasło:</label>
            <input type="password" id="password" name="password" required>
            
            <label for="confirm_password">Potwierdź nowe hasło:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            
            <!-- Lista wymagań dla hasła -->
            <ul id="passwordRequirements" style="list-style-type: none; padding: 0;">
                <li id="requireLength" class="invalid">Minimum 8 znaków</li>
                <li id="requireUpperCase" class="invalid">Co najmniej jedna wielka litera</li>
                <li id="requireLowerCase" class="invalid">Co najmniej jedna mała litera</li>
                <li id="requireNumber" class="invalid">Co najmniej jedna cyfra</li>
                <li id="requireSpecialChar" class="invalid">Co najmniej jeden znak specjalny</li>
                <li id="requireMatch" class="invalid">Hasła muszą się zgadzać</li>
            </ul>

            <button type="submit" class="btn">Zmień hasło</button>
        </form>
    </div>
</div>

<!-- Status Change Password Modal -->
<div id="statusChangePasswordModal" class="modal">
    <div class="modal-content">
        <span class="close" id="statusChangePasswordClose">&times;</span>
        <h3 id="statusChangePasswordTitle"></h3>
        <p id="statusChangePasswordText"></p>
        <button type="button" class="btn" id="statusChangePasswordCloseButton">Zamknij</button>
    </div>
</div>


    <!-- Edit Details Modal -->
    <div id="editDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" id="editDetailsClose">&times;</span>
            <h3>Edytuj Dane</h3>
            <form id="editDetailsForm">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>">
                <label for="username">Nazwa Użytkownika:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>" required>
                <label for="first_name">Imię:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                <label for="last_name">Nazwisko:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                <button type="submit" class="btn">Zapisz Zmiany</button>
            </form>
        </div>
    </div>

    <!-- Success/Error Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close" id="messageClose">&times;</span>
            <h3 id="messageTitle"></h3>
            <p id="messageText"></p>
            <button type="button" class="btn" id="messageCloseButton">Zamknij</button>
        </div>
    </div>

    <script>
        var permissionsModal = document.getElementById("permissionsModal");
        var messageModal = document.getElementById("messageModal");
        var deleteModal = document.getElementById("deleteModal");
        var changePasswordModal = document.getElementById("changePasswordModal");
        var editDetailsModal = document.getElementById("editDetailsModal");

        var permissionsBtn = document.getElementById("permissionsButton");
        var deleteBtn = document.getElementById("deleteButton");
        var changePasswordBtn = document.getElementById("changePasswordButton");
        var editDetailsBtn = document.getElementById("editDetailsButton");
        var blockUserBtn = document.getElementById("blockUserButton");
        var unblockUserBtn = document.getElementById("unblockUserButton");

        var span = document.getElementsByClassName("close")[0];
        var deleteSpan = document.getElementById("deleteClose");
        var changePasswordSpan = document.getElementById("changePasswordClose");
        var editDetailsSpan = document.getElementById("editDetailsClose");
        var messageSpan = document.getElementById("messageClose");

        var messageButton = document.getElementById("messageCloseButton");

        if (permissionsBtn) {
            permissionsBtn.onclick = function() {
                permissionsModal.style.display = "block";
            }
        }

        if (deleteBtn) {
            deleteBtn.onclick = function() {
                deleteModal.style.display = "block";
            }
        }

        if (changePasswordBtn) {
            changePasswordBtn.onclick = function() {
                changePasswordModal.style.display = "block";
            }
        }

        if (editDetailsBtn) {
            editDetailsBtn.onclick = function() {
                editDetailsModal.style.display = "block";
            }
        }

        span.onclick = function() {
            permissionsModal.style.display = "none";
        }

        deleteSpan.onclick = function() {
            deleteModal.style.display = "none";
        }

        changePasswordSpan.onclick = function() {
            changePasswordModal.style.display = "none";
        }

        editDetailsSpan.onclick = function() {
            editDetailsModal.style.display = "none";
        }

        messageSpan.onclick = function() {
            messageModal.style.display = "none";
        }

        messageButton.onclick = function() {
            messageModal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == permissionsModal) {
                permissionsModal.style.display = "none";
            } else if (event.target == deleteModal) {
                deleteModal.style.display = "none";
            } else if (event.target == changePasswordModal) {
                changePasswordModal.style.display = "none";
            } else if (event.target == editDetailsModal) {
                editDetailsModal.style.display = "none";
            } else if (event.target == messageModal) {
                messageModal.style.display = "none";
            }
        }

        document.querySelectorAll('.permission-item').forEach(item => {
            item.addEventListener('click', () => {
                if (item.parentNode.id === 'availablePermissions') {
                    document.getElementById('modalActivePermissions').appendChild(item);
                } else {
                    document.getElementById('availablePermissions').appendChild(item);
                }
            });
        });

        document.getElementById('savePermissionsButton').addEventListener('click', () => {
            var activePermissions = Array.from(document.getElementById('modalActivePermissions').children).map(item => item.dataset.id);
            var userId = "<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>";

            fetch('update_permissions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ user_id: userId, permissions: activePermissions }),
            })
            .then(response => response.json())
            .then(data => {
                permissionsModal.style.display = "none";
                if (data.success) {
                    document.getElementById('messageTitle').innerText = "Sukces";
                    document.getElementById('messageText').innerText = "Uprawnienia zostały zaktualizowane.";
                    refreshActivePermissions(activePermissions);
                } else {
                    document.getElementById('messageTitle').innerText = "Błąd";
                    document.getElementById('messageText').innerText = data.message || "Wystąpił błąd podczas aktualizacji uprawnień.";
                }
                messageModal.style.display = "block";
            })
            .catch(error => {
                console.error('Error:', error);
                permissionsModal.style.display = "none";
                document.getElementById('messageTitle').innerText = "Błąd";
                document.getElementById('messageText').innerText = "Wystąpił błąd podczas aktualizacji uprawnień.";
                messageModal.style.display = "block";
            });
        });

        function refreshActivePermissions(activePermissions) {
            fetch('get_active_permissions.php?user_id=<?php echo $user_id; ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    var activePermissionsList = document.getElementById('activePermissionsList');
                    activePermissionsList.innerHTML = '';
                    data.permissions.forEach(permission => {
                        var div = document.createElement('div');
                        div.className = 'checkbox-container';
                        div.innerHTML = '<span class="checkbox-label">' + permission.name + '</span>';
                        activePermissionsList.appendChild(div);
                    });
                } else {
                    console.error('Error fetching active permissions:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
// Skrypt do obsługi usuwania użytkownika i wyświetlania okna modalnego
function deleteUser(userId) {
    const messageModal = document.getElementById('messageModal'); // Przeniesienie pobrania elementu modalnego na początek funkcji

    fetch('delete_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('messageTitle').innerText = "Sukces";
            document.getElementById('messageText').innerText = data.message || "Użytkownik został pomyślnie usunięty.";
            messageModal.style.display = "block"; // Pokazanie okna modalnego z wiadomością

            // Dodatkowo, ukrycie okna modalnego po kilku sekundach
            setTimeout(() => {
                messageModal.style.display = "none";
            }, 3000); // Ukrycie po 3 sekundach (opcjonalne)
        } else {
            document.getElementById('messageTitle').innerText = "Błąd";
            document.getElementById('messageText').innerText = data.message || "Wystąpił błąd podczas usuwania użytkownika.";
            messageModal.style.display = "block"; // Pokazanie okna modalnego z wiadomością
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('messageTitle').innerText = "Błąd";
        document.getElementById('messageText').innerText = "Wystąpił błąd podczas usuwania użytkownika.";
        messageModal.style.display = "block"; // Pokazanie okna modalnego z wiadomością
    });
}



        // Handle the edit details form submission
        document.getElementById('editDetailsForm').addEventListener('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(event.target);

            fetch('update_user_details.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                editDetailsModal.style.display = "none";
                if (data.success) {
                    document.getElementById('messageTitle').innerText = "Sukces";
                    document.getElementById('messageText').innerText = "Dane użytkownika zostały zaktualizowane.";
                } else {
                    document.getElementById('messageTitle').innerText = "Błąd";
                    document.getElementById('messageText').innerText = data.message || "Wystąpił błąd podczas aktualizacji danych użytkownika.";
                }
                messageModal.style.display = "block";
            })
            .catch(error => {
                console.error('Error:', error);
                editDetailsModal.style.display = "none";
                document.getElementById('messageTitle').innerText = "Błąd";
                document.getElementById('messageText').innerText = "Wystąpił błąd podczas aktualizacji danych użytkownika.";
                messageModal.style.display = "block";
            });
        });

        // Validate password confirmation
        document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                event.preventDefault();
                alert('Hasła się nie zgadzają. Spróbuj ponownie.');
            }
        });

        // Block/Unblock user
        if (blockUserBtn) {
            blockUserBtn.onclick = function() {
                updateBlockStatus(true);
            };
        }

        if (unblockUserBtn) {
            unblockUserBtn.onclick = function() {
                updateBlockStatus(false);
            };
        }

        function updateBlockStatus(block) {
            var userId = "<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>";
            fetch('block_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ user_id: userId, block: block }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (block) {
                        blockUserBtn.style.display = 'none';
                        unblockUserBtn.style.display = 'block';
                    } else {
                        blockUserBtn.style.display = 'block';
                        unblockUserBtn.style.display = 'none';
                    }
                    document.getElementById('messageTitle').innerText = "Sukces";
                    document.getElementById('messageText').innerText = block ? "Konto zostało zablokowane." : "Konto zostało odblokowane.";
                } else {
                    document.getElementById('messageTitle').innerText = "Błąd";
                    document.getElementById('messageText').innerText = data.message || "Wystąpił błąd podczas aktualizacji statusu użytkownika.";
                }
                messageModal.style.display = "block";
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('messageTitle').innerText = "Błąd";
                document.getElementById('messageText').innerText = "Wystąpił błąd podczas aktualizacji statusu użytkownika.";
                messageModal.style.display = "block";
            });
        }
     // Walidacja hasła w czasie rzeczywistym
document.getElementById('password').addEventListener('input', validatePassword);
document.getElementById('confirm_password').addEventListener('input', validatePassword);

function validatePassword() {
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirm_password').value;

    // Regularne wyrażenia do walidacji różnych wymagań
    var lengthRequirement = /.{8,}/;
    var upperCaseRequirement = /[A-Z]/;
    var lowerCaseRequirement = /[a-z]/;
    var numberRequirement = /\d/;
    var specialCharRequirement = /[\W_]/;

    // Walidacja wymagań i zmiana kolorów w zależności od spełnionych kryteriów
    updateRequirementStatus('requireLength', lengthRequirement.test(password));
    updateRequirementStatus('requireUpperCase', upperCaseRequirement.test(password));
    updateRequirementStatus('requireLowerCase', lowerCaseRequirement.test(password));
    updateRequirementStatus('requireNumber', numberRequirement.test(password));
    updateRequirementStatus('requireSpecialChar', specialCharRequirement.test(password));
    updateRequirementStatus('requireMatch', password === confirmPassword);
}

// Funkcja zmieniająca styl wymagań na zielony, jeśli spełnione, i czerwony, jeśli nie
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
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirm_password').value;

    // Jeśli wymagania nie są spełnione, anuluj przesyłanie formularza
    if (!validatePasswordBeforeSubmit(password, confirmPassword)) {
        event.preventDefault();
        alert("Hasło nie spełnia wszystkich wymagań.");
    }
});

function validatePasswordBeforeSubmit(password, confirmPassword) {
    var lengthRequirement = /.{8,}/;
    var upperCaseRequirement = /[A-Z]/;
    var lowerCaseRequirement = /[a-z]/;
    var numberRequirement = /\d/;
    var specialCharRequirement = /[\W_]/;

    // Sprawdź wszystkie wymagania
    return lengthRequirement.test(password) &&
           upperCaseRequirement.test(password) &&
           lowerCaseRequirement.test(password) &&
           numberRequirement.test(password) &&
           specialCharRequirement.test(password) &&
           password === confirmPassword;
}
document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Zatrzymanie domyślnej akcji formularza

    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirm_password').value;

    // Sprawdź, czy wszystkie wymagania są spełnione
    if (validatePasswordBeforeSubmit(password, confirmPassword)) {
        // Wyślij formularz, gdy wszystko jest poprawne
        fetch('change_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                user_id: "<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>",
                password: password
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showChangePasswordStatus(true, "Sukces", "Hasło zostało zmienione pomyślnie.");
            } else {
                showChangePasswordStatus(false, "Błąd", data.message || "Wystąpił błąd podczas zmiany hasła.");
            }
        })
        .catch(error => {
            showChangePasswordStatus(false, "Błąd", "Wystąpił błąd podczas zmiany hasła.");
        });
    } else {
        // Gdy wymagania nie są spełnione
        showChangePasswordStatus(false, "Błąd", "Hasło nie spełnia wszystkich wymagań.");
    }
});

// Funkcja walidacji hasła przed wysłaniem
function validatePasswordBeforeSubmit(password, confirmPassword) {
    var lengthRequirement = /.{8,}/;
    var upperCaseRequirement = /[A-Z]/;
    var lowerCaseRequirement = /[a-z]/;
    var numberRequirement = /\d/;
    var specialCharRequirement = /[\W_]/;

    return lengthRequirement.test(password) &&
           upperCaseRequirement.test(password) &&
           lowerCaseRequirement.test(password) &&
           numberRequirement.test(password) &&
           specialCharRequirement.test(password) &&
           password === confirmPassword;
}

// Funkcja do wyświetlenia statusu zmiany hasła w oknie modalnym
function showChangePasswordStatus(success, title, message) {
    var modal = document.getElementById('statusChangePasswordModal');
    document.getElementById('statusChangePasswordTitle').innerText = title;
    document.getElementById('statusChangePasswordText').innerText = message;
    
    if (success) {
        document.getElementById('statusChangePasswordTitle').style.color = 'green';
    } else {
        document.getElementById('statusChangePasswordTitle').style.color = 'red';
    }

    modal.style.display = 'block';
}

// Obsługa zamknięcia okna modalnego
document.getElementById('statusChangePasswordCloseButton').onclick = function() {
    document.getElementById('statusChangePasswordModal').style.display = 'none';
};

document.getElementById('statusChangePasswordClose').onclick = function() {
    document.getElementById('statusChangePasswordModal').style.display = 'none';
};


    </script>
        <script src="../assets/js/navigation.js"></script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
 