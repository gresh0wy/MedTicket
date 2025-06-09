<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';

// Pobranie liczby otwartych i zakończonych zgłoszeń przypisanych do zalogowanego pracownika
$open_count_sql = "SELECT COUNT(*) FROM tickets WHERE employee_id = ? AND completed_at IS NULL";
$stmt = $conn->prepare($open_count_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($open_count);
$stmt->fetch();
$stmt->close();

$closed_count_sql = "SELECT COUNT(*) FROM tickets WHERE employee_id = ? AND completed_at IS NOT NULL";
$stmt = $conn->prepare($closed_count_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($closed_count);
$stmt->fetch();
$stmt->close();

$items_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'open';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Modyfikacja zapytania SQL z uwzględnieniem terminu wyszukiwania
$search_sql = '';
if (!empty($search)) {
    $search_sql = " AND (name LIKE ? OR description LIKE ? OR department LIKE ?)";
}

if ($status == 'closed') {
    $sql = "SELECT * FROM tickets WHERE employee_id = ? AND completed_at IS NOT NULL" . $search_sql;
} else {
    $sql = "SELECT * FROM tickets WHERE employee_id = ? AND completed_at IS NULL" . $search_sql;
}

if ($filter != 'all') {
    $sql .= " AND category = ?";
}
$sql .= " ORDER BY $sort_column $sort_order LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!empty($search) && $filter != 'all') {
    $search_param = '%' . $search . '%';
    $stmt->bind_param("issssii", $_SESSION['user_id'], $search_param, $search_param, $search_param, $filter, $items_per_page, $offset);
} elseif (!empty($search)) {
    $search_param = '%' . $search . '%';
    $stmt->bind_param("isssii", $_SESSION['user_id'], $search_param, $search_param, $search_param, $items_per_page, $offset);
} elseif ($filter != 'all') {
    $stmt->bind_param("isii", $_SESSION['user_id'], $filter, $items_per_page, $offset);
} else {
    $stmt->bind_param("iii", $_SESSION['user_id'], $items_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Użytkownika</title>
    <link rel="stylesheet" href="assets/css/EmployeePanel.css">
    
    <script>
        function showOpen() {
            window.location.href = '?status=open';
        }

        function showClosed() {
            window.location.href = '?status=closed';
        }
    </script>
</head>
<body>
<header>
    <h1>Panel Użytkownika</h1>
</header>
<?php include 'includes/navigation.php'; ?>

<div class="container">
    <div class="status-cards">
        <div class="status-card <?php echo $status == 'open' ? 'active' : ''; ?>" onclick="showOpen()">
            <div class="details">
                <p><?php echo $open_count; ?></p>
                <h3>Otwarte Zgłoszenia</h3>
            </div>
        </div>
        <div class="status-card closed <?php echo $status == 'closed' ? 'active' : ''; ?>" onclick="showClosed()">
            <div class="details">
                <p><?php echo $closed_count; ?></p>
                <h3>Zrealizowane Zgłoszenia</h3>
            </div>
        </div>
    </div>
    <div class="status-message">
        <?php echo $status == 'closed' ? 'Lista zrealizowanych zgłoszeń' : 'Lista otwartych zgłoszeń'; ?>
    </div>
    <div class="search-bar">
        <form method="get" action="">
            <input type="text" name="search" class="search-input" placeholder="Wyszukaj zgłoszenia" value="<?php echo htmlspecialchars($search); ?>">
            <input type="hidden" name="status" value="<?php echo $status; ?>">
            <input type="hidden" name="page" value="<?php echo $page; ?>">
            <input type="hidden" name="sort" value="<?php echo $sort_column; ?>">
            <input type="hidden" name="order" value="<?php echo $sort_order; ?>">
            <input type="hidden" name="filter" value="<?php echo $filter; ?>">
        </form>
    </div>
    
    <table>
        <tr>
            <th><a href="?status=<?php echo $status; ?>&sort=id&order=<?php echo $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>&filter=<?php echo $filter; ?>&search=<?php echo htmlspecialchars($search); ?>">ID</a></th>
            <th><a href="?status=<?php echo $status; ?>&sort=priority&order=<?php echo $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>&filter=<?php echo $filter; ?>&search=<?php echo htmlspecialchars($search); ?>">Priorytet</a></th>
            <th><a href="?status=<?php echo $status; ?>&sort=category&order=<?php echo $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>&filter=<?php echo $filter; ?>&search=<?php echo htmlspecialchars($search); ?>">Kategoria</a></th>
            <th><a href="?status=<?php echo $status; ?>&sort=department&order=<?php echo $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>&filter=<?php echo $filter; ?>&search=<?php echo htmlspecialchars($search); ?>">Nazwa oddziału</a></th>
            <th><a href="?status=<?php echo $status; ?>&sort=created_at&order=<?php echo $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>&filter=<?php echo $filter; ?>&search=<?php echo htmlspecialchars($search); ?>">Data zgłoszenia</a></th>
            <th>Temat zgłoszenia</th>
            <th>Szczegóły</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="priority-<?php echo strtolower(htmlspecialchars($row['priority'], ENT_QUOTES, 'UTF-8')); ?>">
                    <?php echo htmlspecialchars($row['priority'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td><?php echo htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['department'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="subject-column"><?php echo htmlspecialchars($row['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td> 
                    <a href="tickets/ticket_details.php?id=<?php echo $row['id']; ?>" class="details-button">Szczegóły</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <div class="pagination">
        <?php
        $total_sql = "SELECT COUNT(*) FROM tickets WHERE employee_id = ?";
        if ($status == 'closed') {
            $total_sql .= " AND completed_at IS NOT NULL";
        } else {
            $total_sql .= " AND completed_at IS NULL";
        }
        $stmt = $conn->prepare($total_sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();

        $total_pages = ceil($total / $items_per_page);
        ?>

        <h3>Strony <?php echo $status == 'closed' ? 'zrealizowanych' : 'otwartych'; ?> zgłoszeń:</h3>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?status=<?php echo $status; ?>&page=<?php echo $i; ?>&sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>&filter=<?php echo $filter; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</div>
<script src="assets/js/navigation.js"></script>

</body>
</html>

<?php
$conn->close();
?>
