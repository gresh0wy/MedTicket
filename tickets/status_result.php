<?php
include('../includes/db.php');

$category = $_POST['category'];
$description = $_POST['description'];

// Przykładowe wstawienie danych do bazy
$sql = "INSERT INTO tickets (category, description) VALUES ('$category', '$description')";
if ($conn->query($sql) === TRUE) {
    $ticket_id = $conn->insert_id;
    echo "Zgłoszenie zostało wysłane. Numer zgłoszenia: " . $ticket_id;
} else {
    echo "Błąd: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
