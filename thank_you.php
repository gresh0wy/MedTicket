<?php
session_start();
$ticket_id = isset($_SESSION['ticket_id']) ? $_SESSION['ticket_id'] : null;
unset($_SESSION['ticket_id']);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedTicket - Dziękujemy</title>
    <link rel="stylesheet" href="assets/css/MainPageStyle.css">
</head>
<body>
    <header>
        <h1>MedTicket</h1>
    </header>
    <?php include 'includes/navigation.php'; ?>
    <div class="container">
        <h2>Dziękujemy za zgłoszenie!</h2>
        <p>Twoje zgłoszenie zostało pomyślnie wysłane. Skontaktujemy się z Tobą wkrótce.</p>
         <p>Zapisz numer zgłoszenia, aby móc sprawdzić jego status.</p>
        <?php if ($ticket_id): ?>
            <p>Numer zgłoszenia: <strong><?php echo htmlspecialchars($ticket_id, ENT_QUOTES, 'UTF-8'); ?></strong></p>
           
        <?php endif; ?>
    </div>
    <script></script>
</body>
</html>
