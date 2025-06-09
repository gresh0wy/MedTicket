<?php
session_start();

// Połączenie z bazą danych
include '../includes/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="pl" style="font-size:62.5%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprawdź status zgłoszenia</title>
    <link rel="stylesheet" href="../assets/css/FormStyle.css">
</head>
<body>
    <header>
        <h1>MedTicket</h1>
    </header>

    <?php include '../tickets/includes/navigation.php'; ?>

    <div class="grid-container">
        <!-- Lewy panel: formularz wyszukiwania -->
        <div class="search-panel">
            <h2>Sprawdź status zgłoszenia</h2>
            <form action="check_status.php" method="post" class="search-form">
                <input type="text" id="ticket_id" name="ticket_id" placeholder="Numer zgłoszenia" required>
                <button type="submit">Sprawdź status</button>
            </form>
        </div>

        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
            <?php
            $ticket_id = intval($_POST['ticket_id']);

            if ($ticket_id <= 0) {
                echo "<p class='error-msg'>Nieprawidłowy numer zgłoszenia.</p>";
            } else {
                // 1) Pobranie danych zgłoszenia
                $sql = "
                    SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) AS assigned_user
                    FROM tickets t
                    LEFT JOIN users u ON t.employee_id = u.id
                    WHERE t.id = ?
                ";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $ticket_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

                if ($result->num_rows > 0):
                    $ticket      = $result->fetch_assoc();
                    $description = $ticket['description'] ?? '';
                    $assigned    = $ticket['assigned_user'] ?? 'Nie przydzielono';

                    // 2) Pobranie komentarzy
                    $comments_sql = "
                        SELECT tc.*, CONCAT(u.first_name, ' ', u.last_name) AS author_name
                        FROM ticket_comments tc
                        JOIN users u ON tc.user_id = u.id
                        WHERE tc.ticket_id = ?
                        ORDER BY tc.created_at ASC
                    ";
                    $cstmt = $conn->prepare($comments_sql);
                    $cstmt->bind_param("i", $ticket_id);
                    $cstmt->execute();
                    $comments = $cstmt->get_result();
                    $cstmt->close();
                    ?>

                    <div class="griditem-1">
    <div class="ts-header">
        <p class="ts-field-label">Numer zgłoszenia: <span class="ts-field-value"><?= htmlspecialchars($ticket['id']) ?></span></p>
         <?php
        $status_raw = strtolower($ticket['status']);
        $status_class = match($status_raw) {
            'nowe'         => 'status-nowe',
            'w trakcie'    => 'status-w-trakcie',
            'zakończone'   => 'status-zakonczone',
            'odrzucono'    => 'status-odrzucono',
            default        => 'status-all',
        };
        ?>
        <p class="ts-field-label">Status: 
          <span class="status-badge <?= $status_class ?>">
            <?= htmlspecialchars($ticket['status']) ?>
          </span>
        </p>
        
        <p class="ts-field-label">Imię i nazwisko: <span class="ts-field-value"><?= htmlspecialchars($ticket['name']) ?></span></p>
        
        
       
    </div>
    <p class="ts-field-label">Temat: <span class="ts-field-value"><?= htmlspecialchars($ticket['subject']) ?></span></p>
    <div class="ts-description">
        <p class="ts-field-label">Opis:</p>
        <div class="description-box">
            <?= nl2br(htmlspecialchars($description)) ?>
        </div>
    </div>
</div>


                    <div class="griditem-2">
                         
                        <h3>Czynności wykonane:</h3>
                        <?php if ($comments->num_rows): ?>
                            <ul class="comments-list">
                                <?php while ($c = $comments->fetch_assoc()):
                                    $created = (new DateTime($c['created_at']))->format('Y-m-d H:i');
                                    $edited  = $c['updated_at']
                                        ? ' (edytowano ' . (new DateTime($c['updated_at']))->format('Y-m-d H:i') . ')'
                                        : '';
                                    ?>
                                    <li class="comment-item">
                                        <div class="comment-header">
                                            <strong><?= htmlspecialchars($c['author_name'], ENT_QUOTES) ?></strong>
                                            <span class="comment-date"><?= $created . $edited ?></span>
                                        </div>
                                        <div class="comment-body">
                                            <?= nl2br(htmlspecialchars($c['comment'], ENT_QUOTES)) ?>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="msg-com">Brak komentarzy dla tego zgłoszenia.</p>
                            
                        <?php endif; ?>
                        <div class="ts-assigned">
                            <p class="">Zgłoszeniem Zostało przypisane do: <span> <strong><?= htmlspecialchars($assigned) ?></strong></span></p>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="griditem-1">
                        <p class="error-msg">Nie znaleziono zgłoszenia o podanym numerze.</p>
                    </div>
                <?php endif; ?>
            <?php } ?>
        <?php endif; ?>
    </div>

    <script src="../assets/js/navigation.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
