<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $section = $_POST['section'];
    $category = $_POST['category'];
    $department = $_POST['department'];
    $contact_number = $_POST['contact_number'];
    $description = $_POST['description'];
    $priority = $_POST['priority'];
    $recurrence = $_POST['recurrence'];
    $subject = $_POST['subject'];
    $employee_id = empty($_POST['employee_id']) ? null : $_POST['employee_id'];


    
    // Domyślny status zgłoszenia
    $default_status = 'Nowe';
    
    // Przygotowanie zapytania SQL, uwzględniając przypisanie pracownika
    $stmt = $conn->prepare("INSERT INTO tickets (name, section, category, department, contact_number, description, priority, recurrence, subject, status, created_at, employee_id) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("sssssssssss", $name, $section, $category, $department, $contact_number, $description, $priority, $recurrence, $subject, $default_status, $employee_id);

    if ($stmt->execute()) {
        $ticket_id = $stmt->insert_id; // Pobranie numeru zgłoszenia
        $_SESSION['ticket_id'] = $ticket_id; // Zapisanie numeru zgłoszenia w sesji
        
        // Przekierowanie na stronę z podziękowaniem
        header("Location: ../thank_you.php");
        exit();
    } else {
        // Wyświetlenie błędu
        echo "Błąd: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>
