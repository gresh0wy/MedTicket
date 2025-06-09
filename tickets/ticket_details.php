<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Konfiguracja uprawnień:
// true  – zwykły użytkownik może edytować/usunąć własne komentarze
// false – tylko superadmin
$user_can_modify_own = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Aktualizacja status, sekcji, priorytetu, działań
    if (isset($_POST['status'])) {
        $status = $_POST['status'];
        $update_sql = "UPDATE tickets SET status = ?, completed_at = NULL WHERE id = ?";
        if ($status === 'Zakończone' || $status === 'Odrzucono') {
            $update_sql = "UPDATE tickets SET status = ?, completed_at = NOW() WHERE id = ?";
        }
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $status, $ticket_id);
        $stmt->execute();
        $stmt->close();
    }
    if (isset($_POST['section'])) {
        $section = $_POST['section'];
        $stmt = $conn->prepare("UPDATE tickets SET section = ? WHERE id = ?");
        $stmt->bind_param("si", $section, $ticket_id);
        $stmt->execute();
        $stmt->close();
    }
    if (isset($_POST['priority'])) {
        $priority = $_POST['priority'];
        $stmt = $conn->prepare("UPDATE tickets SET priority = ? WHERE id = ?");
        $stmt->bind_param("si", $priority, $ticket_id);
        $stmt->execute();
        $stmt->close();
    }
    if (isset($_POST['actions'])) {
        $actions = trim($_POST['actions']);
        $stmt = $conn->prepare("UPDATE tickets SET actions = ? WHERE id = ?");
        $stmt->bind_param("si", $actions, $ticket_id);
        $stmt->execute();
        $stmt->close();
    }

    // 2) Przypisanie pracownika + logowanie historii (tylko gdy wybrano)
    if (isset($_POST['employee_id']) && $_POST['employee_id'] !== '') {
        $new_emp    = (int)$_POST['employee_id'];
        $changed_by = $_SESSION['user_id'];

        // Pobranie poprzedniego przypisania
        $old_stmt = $conn->prepare("SELECT employee_id FROM tickets WHERE id = ?");
        $old_stmt->bind_param("i", $ticket_id);
        $old_stmt->execute();
        $old_emp = $old_stmt->get_result()->fetch_assoc()['employee_id'];
        $old_stmt->close();

        // Wpis do historii tylko, gdy zmiana
        if ($old_emp !== $new_emp) {
            $hist = $conn->prepare(
                "INSERT INTO ticket_assignment_history
                   (ticket_id, previous_employee_id, new_employee_id, changed_by)
                 VALUES (?, ?, ?, ?)"
            );
            $hist->bind_param("iiii", $ticket_id, $old_emp, $new_emp, $changed_by);
            $hist->execute();
            $hist->close();
        }

        // Aktualizacja głównego ticketu
        $upd = $conn->prepare("UPDATE tickets SET employee_id = ? WHERE id = ?");
        $upd->bind_param("ii", $new_emp, $ticket_id);
        $upd->execute();
        $upd->close();
    }

    // 3) Dodanie komentarza w tym samym formularzu
    if (!empty(trim($_POST['new_comment']))) {
        $new_comment = trim($_POST['new_comment']);
        $user_id      = $_SESSION['user_id'];
        $stmt = $conn->prepare(
            "INSERT INTO ticket_comments (ticket_id, user_id, comment)
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iis", $ticket_id, $user_id, $new_comment);
        $stmt->execute();
        $stmt->close();
    }

    // 4) Edycja komentarzy
    if (isset($_POST['edit_comment_id'], $_POST['edited_comment'])) {
        $comment_id   = (int)$_POST['edit_comment_id'];
        $edited_text  = trim($_POST['edited_comment']);
        $current_user = $_SESSION['user_id'];
        $role         = $_SESSION['role'] ?? '';

        $chk = $conn->prepare("SELECT user_id FROM ticket_comments WHERE id = ?");
        $chk->bind_param("i", $comment_id);
        $chk->execute();
        $owner = $chk->get_result()->fetch_assoc()['user_id'];
        $chk->close();

        if ($role === 'Super Administrator' || ($owner === $current_user && $user_can_modify_own)) {
            if ($edited_text !== '') {
                $u = $conn->prepare(
                    "UPDATE ticket_comments
                     SET comment = ?, updated_at = NOW()
                     WHERE id = ?"
                );
                $u->bind_param("si", $edited_text, $comment_id);
                $u->execute();
                $u->close();
            }
        }
    }

    // 5) Usuwanie komentarzy
    if (isset($_POST['delete_comment_id'])) {
        $comment_id   = (int)$_POST['delete_comment_id'];
        $current_user = $_SESSION['user_id'];
        $role         = $_SESSION['role'] ?? '';

        $chk = $conn->prepare("SELECT user_id FROM ticket_comments WHERE id = ?");
        $chk->bind_param("i", $comment_id);
        $chk->execute();
        $owner = $chk->get_result()->fetch_assoc()['user_id'];
        $chk->close();

        if ($role === 'Super Administrator' || ($owner === $current_user && $user_can_modify_own)) {
            $d = $conn->prepare("DELETE FROM ticket_comments WHERE id = ?");
            $d->bind_param("i", $comment_id);
            $d->execute();
            $d->close();
        }
    }

    header("Location: ticket_details.php?id={$ticket_id}");
    exit();
}

// Pobranie zgłoszenia
$stmt = $conn->prepare(
    "SELECT t.*, CONCAT(u.first_name,' ',u.last_name) AS employee_name
     FROM tickets t
     LEFT JOIN users u ON t.employee_id = u.id
     WHERE t.id = ?"
);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->num_rows ? $result->fetch_assoc() : null;
$stmt->close();

// Pobranie komentarzy
$comments_stmt = $conn->prepare(
    "SELECT tc.*, CONCAT(u.first_name,' ',u.last_name) AS author_name
     FROM ticket_comments tc
     JOIN users u ON tc.user_id = u.id
     WHERE tc.ticket_id = ?
     ORDER BY tc.created_at ASC"
);
$comments_stmt->bind_param("i", $ticket_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
$comments_stmt->close();

// Pobranie historii przypisań
$as_hist = $conn->prepare(
    "SELECT tah.*,
            CONCAT(u1.first_name,' ',u1.last_name) AS prev_name,
            CONCAT(u2.first_name,' ',u2.last_name) AS new_name,
            CONCAT(u3.first_name,' ',u3.last_name) AS changed_by_name
     FROM ticket_assignment_history tah
     LEFT JOIN users u1 ON tah.previous_employee_id = u1.id
     JOIN users u2 ON tah.new_employee_id = u2.id
     JOIN users u3 ON tah.changed_by = u3.id
     WHERE tah.ticket_id = ?
     ORDER BY tah.changed_at DESC"
);
$as_hist->bind_param("i", $ticket_id);
$as_hist->execute();
$assignment_history = $as_hist->get_result();
$as_hist->close();

// Pobranie listy użytkowników
$users_result = $conn->query(
    "SELECT id, CONCAT(first_name,' ',last_name) AS full_name
     FROM users"
);

$conn->close();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szczegóły zgłoszenia - MedTicket</title>
    <link rel="stylesheet" href="../assets/css/TicketDetails.css">
</head>
<body>
    <header>
        <h1>Szczegóły zgłoszenia</h1>
    </header>
    <?php include '../tickets/includes/navigation.php'; ?>
    <div class="container">
        <?php if ($ticket): ?>
            <div class="left-panel">
                <div class="ticket-details-card">
                    <h2>Zgłoszenie #<?= htmlspecialchars($ticket['id'], ENT_QUOTES) ?></h2>
                    <div class="ticket-info">
                        <p><strong>Imię i Nazwisko:</strong> <?= htmlspecialchars($ticket['name'], ENT_QUOTES) ?></p>
                        <p><strong>Sekcja:</strong> <?= htmlspecialchars($ticket['section'], ENT_QUOTES) ?></p>
                        <p><strong>Kategoria:</strong> <?= htmlspecialchars($ticket['category'], ENT_QUOTES) ?></p>
                        <p><strong>Oddział:</strong> <?= htmlspecialchars($ticket['department'], ENT_QUOTES) ?></p>
                        <p><strong>Kontakt:</strong> <?= htmlspecialchars($ticket['contact_number'], ENT_QUOTES) ?></p>
                    </div>
                    <div>
                        <h3>Temat zgłoszenia</h3>
                        <div class="subject-container">
                            <?= nl2br(htmlspecialchars($ticket['subject'], ENT_QUOTES)) ?>
                        </div>
                    </div>
                    <div>
                        <h3>Opis problemu</h3>
                        <div class="description-container">
                            <?= nl2br(htmlspecialchars($ticket['description'], ENT_QUOTES)) ?>
                        </div>
                    </div>

                    <!-- Historia przypisań -->
                  <div class="assignment-history">
    <h3>Historia przypisań</h3>
    <?php if ($assignment_history->num_rows): ?>
    <ul id="assignment-list">
        <?php 
            $counter = 0;
            while($h = $assignment_history->fetch_assoc()):
                $counter++;
                $hiddenClass = $counter > 5 
                    ? 'class="extra-assignment" style="display:none;"' 
                    : '';
        ?>
        <li <?= $hiddenClass ?>>
            <div class="assignment-card">
                <div class="assignment-main">
                    <strong><?= $h['prev_name'] ?: 'Brak' ?> &rarr; <?= $h['new_name'] ?></strong>
                    <span class="history-date"><?= (new DateTime($h['changed_at']))->format('Y-m-d H:i') ?></span>
                </div>
                <div class="assignment-meta">zmienił: <?= $h['changed_by_name'] ?></div>
            </div>
        </li>
        <?php endwhile; ?>
    </ul>
    <?php if ($counter > 5): ?>
    <div id="history-buttons">
        <button id="show-more-btn">Pokaż więcej</button>
        <button id="show-less-btn" style="display:none;">Pokaż mniej</button>
    </div>
    <?php endif; ?>
    <?php else: ?>
        <p>Brak zmian pracownika.</p>
    <?php endif; ?>
</div>


                    <!-- Komentarze -->
                    <div class="comments-section">
                        <h3>Wykonane czynności</h3>
                        <?php if ($comments_result->num_rows): ?>
                        <ul class="comments-list">
                            <?php while ($c = $comments_result->fetch_assoc()):
                                $is_owner   = ($c['user_id'] === $_SESSION['user_id']);
                                $is_super   = ($_SESSION['role'] === 'Super Administrator');
                                $can_edit   = $is_super || ($is_owner && $user_can_modify_own);
                                $can_delete = $can_edit;
                            ?>
                            <li class="comment-item">
                                <div class="comment-header">
                                    <strong><?= htmlspecialchars($c['author_name'], ENT_QUOTES) ?></strong>
                                    <span class="comment-date">
                                        <?= (new DateTime($c['created_at']))->format('Y-m-d H:i') ?>
                                        <?= $c['updated_at'] 
                                            ? ' (edytowano '.(new DateTime($c['updated_at']))->format('Y-m-d H:i').')' 
                                            : '' ?>
                                    </span>
                                </div>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $c['id'] && $can_edit): ?>
                                <form method="post" class="edit-comment-form">
                                    <input type="hidden" name="edit_comment_id" value="<?= $c['id'] ?>">
                                    <textarea name="edited_comment" rows="3"><?= htmlspecialchars($c['comment'], ENT_QUOTES) ?></textarea>
                                    <button type="submit" class="btn-update">Zapisz</button>
                                    <a href="ticket_details.php?id=<?= $ticket_id ?>" class="btn-cancel">Anuluj</a>
                                </form>
                                <?php else: ?>
                                <div class="comment-body">
                                    <?= nl2br(htmlspecialchars($c['comment'], ENT_QUOTES)) ?>
                                </div>
                                <div class="comment-actions">
                                    <?php if ($can_edit): ?>
                                    <a href="ticket_details.php?id=<?= $ticket_id ?>&edit=<?= $c['id'] ?>" class="btn-small">Edytuj</a>
                                    <?php endif; ?>
                                    <?php if ($can_delete): ?>
                                    <form method="post" class="delete-comment-form" onsubmit="return confirm('Usunąć komentarz?');">
                                        <input type="hidden" name="delete_comment_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn-small btn-danger">Usuń</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                        <?php else: ?>
                            <p>Brak komentarzy.</p>
                        <?php endif; ?>

                        <!-- Usunięty oddzielny formularz; komentarz dodawany przy Zapisz -->
                    </div>
                </div>
            </div>

            <div class="right-panel">
                <div class="ticket-info">
                    <p><strong>Priorytet:</strong> <?= htmlspecialchars($ticket['priority'], ENT_QUOTES) ?></p>
                    <p><strong>Powtarzalność:</strong> <?= htmlspecialchars($ticket['recurrence'], ENT_QUOTES) ?></p>
                    <p><strong>Data zgłoszenia:</strong> <?= (new DateTime($ticket['created_at']))->format('Y-m-d H:i') ?></p>
                    <p><strong>Data zakończenia:</strong>
                        <?= $ticket['completed_at']
                            ? (new DateTime($ticket['completed_at']))->format('Y-m-d H:i')
                            : 'Nie zakończono' ?>
                    </p>
                    <p><strong>Pracownik:</strong> <?= htmlspecialchars($ticket['employee_name'] ?: 'Nie przypisano', ENT_QUOTES) ?></p>
                </div>

                <div class="form-container">
                    <form action="" method="post" id="ticket-form" class="ticket-form">
                        <!-- Rząd 1: Status + Sekcja -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Status:</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="Nowe"     <?= $ticket['status']==='Nowe'     ? 'selected':'' ?>>Nowe</option>
                                    <option value="W trakcie"<?= $ticket['status']==='W trakcie'? 'selected':'' ?>>W trakcie</option>
                                    <option value="Zakończone"<?= $ticket['status']==='Zakończone'? 'selected':'' ?>>Zakończone</option>
                                    <option value="Odrzucono"<?= $ticket['status']==='Odrzucono'? 'selected':'' ?>>Odrzucono</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="section">Sekcja:</label>
                                <select name="section" id="section" class="form-control">
                                    <option value="informatyczna" <?= $ticket['section']==='informatyczna'?'selected':'' ?>>Informatyczna</option>
                                    <option value="elektryczna"   <?= $ticket['section']==='elektryczna'?'selected':'' ?>>Elektryczna</option>
                                    <option value="cyber"         <?= $ticket['section']==='cyber'?'selected':'' ?>>Cyber</option>
                                    <option value="budowlana"     <?= $ticket['section']==='budowlana'?'selected':'' ?>>Budowlana</option>
                                    <option value="aparatura"     <?= $ticket['section']==='aparatura'?'selected':'' ?>>Aparatura</option>
                                </select>
                            </div>
                        </div>
                        <!-- Rząd 2: Priorytet + Pracownik -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="priority">Priorytet:</label>
                                <select name="priority" id="priority" class="form-control">
                                    <option disabled <?= !$ticket['priority']?'selected':'' ?>>Wybierz</option>
                                    <option value="Niski"    <?= $ticket['priority']==='Niski'?'selected':'' ?>>Niski</option>
                                    <option value="Średni"   <?= $ticket['priority']==='Średni'?'selected':'' ?>>Średni</option>
                                    <option value="Wysoki"   <?= $ticket['priority']==='Wysoki'?'selected':'' ?>>Wysoki</option>
                                    <option value="Krytyczny"<?= $ticket['priority']==='Krytyczny'?'selected':'' ?>>Krytyczny</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="employee_id">Pracownik:</label>
                                <select name="employee_id" id="employee_id" class="form-control">
                                    <option value="">Wybierz</option>
                                    <?php while($user = $users_result->fetch_assoc()): ?>
                                    <option value="<?= $user['id'] ?>" <?= $ticket['employee_id']===$user['id']?'selected':''?>>
                                        <?= htmlspecialchars($user['full_name'], ENT_QUOTES) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                                <span id="employee-error" class="error-message">Proszę wybrać pracownika.</span>
                            </div>
                        </div>
                       
                        <div class="form-group">
                            <label for="new_comment">Czynności wykonane:</label>
                            <textarea name="new_comment" id="new_comment" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-save">Zapisz zmiany</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <p>Zgłoszenie nie zostało znalezione.</p>
        <?php endif; ?>
    </div>

    <div id="section-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p id="modal-text">Czy na pewno chcesz przenieść zgłoszenie do tej sekcji?</p>
            <button id="confirm-change-section">Tak</button>
            <button id="cancel-change-section">Nie</button>
        </div>
    </div>

    <script src="../assets/js/navigation.js"></script>
 <script>
    const showMoreBtn = document.getElementById('show-more-btn');
    const showLessBtn = document.getElementById('show-less-btn');
    const extras = document.querySelectorAll('.extra-assignment');

    if (showMoreBtn && showLessBtn) {
        showMoreBtn.addEventListener('click', () => {
            extras.forEach(el => el.style.display = 'block');
            showMoreBtn.style.display = 'none';
            showLessBtn.style.display = 'inline-block';
        });

        showLessBtn.addEventListener('click', () => {
            extras.forEach(el => el.style.display = 'none');
            showMoreBtn.style.display = 'inline-block';
            showLessBtn.style.display = 'none';
        });
    }
</script>

</body>
</html>
