<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle message deletion
if (isset($_POST['delete'])) {
    $filePath = '../data/message.txt';
    if (file_exists($filePath)) {
        unlink($filePath);
        $messageDeleted = "Komunikat został usunięty.";
    } else {
        $messageDeleted = "Brak komunikatu do usunięcia.";
    }
}

// Handle message submission
if (isset($_POST['submit'])) {
    $message = $_POST['message'];
    $dataDir = '../data';
    $filePath = $dataDir . '/message.txt';

    // Ensure the data directory exists and is writable
    if (!file_exists($dataDir)) {
        if (!mkdir($dataDir, 0777, true)) {
            die('Failed to create directories...');
        }
    }

    if (is_writable($dataDir)) {
        file_put_contents($filePath, $message);
        $messageSaved = "Komunikat został zapisany.";
    } else {
        $messageSaved = "Nie można zapisać komunikatu. Sprawdź uprawnienia katalogu.";
    }
}

// Pobierz uprawnienia użytkownika z sesji
$permissions = $_SESSION['permissions'] ?? [];

// Sprawdź, czy użytkownik ma odpowiednie uprawnienia
if (!in_array('Super Administrator', $permissions) && !in_array('Administrator', $permissions)) {
    header("Location: ../login.php");
    exit();
}
?>
    
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedTicket - Panel Administratora</title>
    <link rel="stylesheet" href="../assets/css/AdminPanelStyle.css">
    <style>
     
    </style>
</head>
<body>
    <header>
        <h1>MedTicket - Panel Administratora</h1>
    </header>
    <?php include '../admin/includes/navigation.php'; ?>


    <div class="container">
        <h2>Komunikat o awarii</h2>
        <?php
        if (isset($messageSaved)) {
            echo "<p>$messageSaved</p>";
        }
        if (isset($messageDeleted)) {
            echo "<p>$messageDeleted</p>";
        }
        ?>

        <button id="openModalBtn">Dodaj Komunikat</button>
        <form action="announcement.php" method="post" style="margin-top: 10px;">
            <button type="submit" name="delete">Usuń komunikat</button>
        </form>
    </div>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <form action="announcement.php" method="post">
                <label for="message">Komunikat:</label>
                <textarea id="message" name="message" rows="4" required></textarea>
                <button type="submit" name="submit">Wyślij komunikat</button>
            </form>
        </div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("myModal");

        // Get the button that opens the modal
        var btn = document.getElementById("openModalBtn");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks the button, open the modal
        btn.onclick = function() {
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
       
    
        
    </script>
    <script src="../assets/js/navigation.js"></script>
    </body>
</html>
