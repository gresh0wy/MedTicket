<?php
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $department = $_POST['department'];
    $contact_number = $_POST['contact_number'];
    $description = $_POST['description'];
    $priority = $_POST['priority'];
    $recurrence = $_POST['recurrence'];
    
    $sql = "INSERT INTO tickets (name, category, department, contact_number, description, priority, recurrence, created_at) VALUES ('$name', '$category', '$department', '$contact_number', '$description', '$priority', '$recurrence', NOW())";

    if ($conn->query($sql) === TRUE) {
        header("Location: success_page.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nowe zgłoszenie - MedTicket</title>
    <link rel="stylesheet" href="../assets/css/FormStyle.css">
</head>
<body>
    <header>
        <h1>Nowe zgłoszenie</h1>
    </header>
    <nav>
        <a href="../index.php">Strona główna</a>
        <a href="new_ticket.php">Zgłoś problem</a>
        <a href="check_status.php">Sprawdź status zgłoszenia</a>
        <a href="../admin/index.php">Admin - Strona główna</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../logout.php">Wyloguj się</a>
        <?php else: ?>
            <a href="../login.php">Zaloguj się</a>
        <?php endif; ?>
    </nav>
    <div class="container">
        <h2>Zgłoś problem</h2>
        <form action="submit_ticket.php" method="post">
            <label for="name">Imię i Nazwisko:</label>
            <input type="text" id="name" name="name" required>
            
            <label for="category">Kategoria zgłoszenia:</label>
            <select id="category" name="category" required>
                <option value="">Wybierz</option>
                <option value="konta">Konta</option>
                <option value="amms">AMMS</option>
                <option value="komputer">Komputer</option>
                <option value="drukarka">Drukarka</option>
            </select>
            
            <label for="department">Nazwa oddziału:</label>
            <input type="text" id="department" name="department" required>
            
            <label for="contact_number">Numer kontaktowy:</label>
            <input type="text" id="contact_number" name="contact_number" required>
            
            <label for="description">Opis problemu:</label>
            <textarea id="description" name="description" rows="4" required></textarea>
            
            <label for="priority">Stopień priorytetu:</label>
            <select id="priority" name="priority" required>
                <option value="">Wybierz</option>
                <option value="niski">Niski</option>
                <option value="średni">Średni</option>
                <option value="wysoki">Wysoki</option>
            </select>
            
            <label for="recurrence">Powtarzalność problemu:</label>
            <select id="recurrence" name="recurrence" required>
                <option value="">Wybierz</option>
                <option value="jednorazowy">Jednorazowy</option>
                <option value="sporadyczny">Sporadyczny</option>
                <option value="częsty">Częsty</option>
            </select>
            
            <button type="submit">Wyślij zgłoszenie</button>
        </form>
    </div>
</body>
</html>
