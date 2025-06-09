<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

// Pobierz uprawnienia użytkownika
$user_id = $_SESSION['user_id'];
$permissions = [];

$permissions_sql = "SELECT p.name FROM user_permissions up
                    JOIN permissions p ON up.permission_id = p.id
                    WHERE up.user_id = ?";
$stmt = $conn->prepare($permissions_sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $permissions[] = $row['name'];
}

// Sprawdź, czy użytkownik ma odpowiednie uprawnienia
if (!in_array('Obserwator', $permissions) && !in_array('Sekcja Elektryczna', $permissions)) {
    header("Location: ../login.php");
    exit();
}

// Pobierz wszystkie kategorie przypisane do sekcji elektrycznej
$categories = [];
$categories_sql = "SELECT DISTINCT category FROM tickets WHERE section = 'elektryczna'";
$categories_result = $conn->query($categories_sql);

if ($categories_result->num_rows > 0) {
    while ($category_row = $categories_result->fetch_assoc()) {
        $categories[] = $category_row['category'];
    }
}

// Pobierz wszystkie oddziały przypisane do sekcji elektrycznej
$departments = [];
$departments_sql = "SELECT DISTINCT department FROM tickets WHERE section = 'elektryczna'";
$departments_result = $conn->query($departments_sql);

if ($departments_result->num_rows > 0) {
    while ($department_row = $departments_result->fetch_assoc()) {
        $departments[] = $department_row['department'];
    }
}

// Pobierz wartość filtru, jeśli istnieje
$filter = isset($_GET['filter']) ? urldecode($_GET['filter']) : 'all';
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$sort_order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'ASC';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : [];

// Pobierz wyszukiwanie i filtry pracowników
$search = isset($_GET['search']) ? $_GET['search'] : '';
$selected_employees = isset($_GET['employees']) ? $_GET['employees'] : [];
$selected_categories = isset($_GET['categories']) ? array_map('urldecode', $_GET['categories']) : [];
$selected_department = isset($_GET['department']) ? urldecode($_GET['department']) : '';
$selected_priorities = isset($_GET['priorities']) ? $_GET['priorities'] : [];

// Pobierz zgłoszenia z bazy danych z filtrowaniem i sortowaniem
$sql = "SELECT tickets.*, CONCAT(users.first_name, ' ', users.last_name) as employee_name FROM tickets 
        LEFT JOIN users ON tickets.employee_id = users.id 
        WHERE tickets.section = 'elektryczna'";

if (!empty($selected_categories)) {
    $category_filter = "'" . implode("','", array_map([$conn, 'real_escape_string'], $selected_categories)) . "'";
    $sql .= " AND tickets.category IN ($category_filter)";
}

if ($selected_department) {
    $sql .= " AND tickets.department = '" . $conn->real_escape_string($selected_department) . "'";
}

if (!empty($selected_priorities)) {
    $priority_filter = "'" . implode("','", array_map([$conn, 'real_escape_string'], $selected_priorities)) . "'";
    $sql .= " AND tickets.priority IN ($priority_filter)";
}

if (!empty($status_filter)) {
    $status_filter_sql = "'" . implode("','", array_map([$conn, 'real_escape_string'], $status_filter)) . "'";
    $sql .= " AND tickets.status IN ($status_filter_sql)";
}

if ($search) {
    $sql .= " AND (tickets.name LIKE '%" . $conn->real_escape_string($search) . "%' OR 
                CONCAT(users.first_name, ' ', users.last_name) LIKE '%" . $conn->real_escape_string($search) . "%' OR 
                tickets.department LIKE '%" . $conn->real_escape_string($search) . "%')";
}

if (!empty($selected_employees)) {
    $employee_ids = implode(',', array_map('intval', $selected_employees));
    $sql .= " AND tickets.employee_id IN ($employee_ids)";
}

$sql .= " ORDER BY $sort_column $sort_order";
$result = $conn->query($sql);

if ($conn->error) {
    echo "Błąd: " . $conn->error;
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zgłoszenia - Sekcja Elektryczna</title>
    <link rel="stylesheet" href="../assets/css/section.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin-left: 20px; /* Dodanie miejsca na wąski pasek */
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            margin-top: 10px;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .user-menu {
            display: inline-block;
            position: relative;
        }

        .user-name {
            cursor: pointer;
            font-weight: bold;
            color: white;
        }

        .user-name::after {
            content: ' ▼';
            font-size: 0.8em;
        }

        .filter-buttons {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-button, .search-form button {
            padding: 10px 20px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }

        .filter-button:hover, .search-form button:hover {
            background-color: #0056b3;
        }

        .search-form {
            display: flex;
            align-items: center;
        }

        .search-form input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            margin-right: 10px;
            width: 200px;
        }

        .employee-list, .category-list {
            max-height: 200px;
            overflow-y: auto;
            text-align: left;
            margin-bottom: 20px;
        }

        .employee-list label, .category-list label {
            display: block;
            padding: 5px;
        }

        .employee-list label:hover, .category-list label:hover {
            background-color: #f1f1f1;
        }

        .employee-list input[type="checkbox"], .category-list input[type="checkbox"] {
            margin-right: 10px;
        }

        .filter-sidebar {
            position: fixed;
            left: -300px;
            top: 0;
            width: 300px;
            height: 100%;
            background-color: #f8f9fa;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            transition: left 0.3s ease;
            overflow-y: auto;
        }

        .filter-sidebar.open {
            left: 0;
        }

        .filter-sidebar h2 {
            margin-top: 0;
        }

        .filter-sidebar h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.2em;
        }

        .filter-sidebar .toggle-btn {
            position: absolute;
            top: 10px;
            right: 2px;
            font-size: 28px;
            cursor: pointer;
            background: none;
            border: none;
            color: #333;
        }

        .filter-sidebar .toggle-btn.open::after {
            content: '✖';
            margin-left: 5px; /* Przesunięcie ikony o 5px w prawo */
        }

        .filter-sidebar .toggle-btn.closed::after {
            content: '☰';
            margin-left: -5px; /* Przesunięcie ikony bliżej krawędzi */
        }

        .filter-sidebar .filter-section {
            margin-bottom: 20px;
        }

        .filter-sidebar .filter-submit {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .filter-sidebar .filter-submit button {
            padding: 10px 20px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }

        .filter-sidebar .filter-submit button:hover {
            background-color: #0056b3;
        }

        .filter-sidebar .priority-filter, .filter-sidebar .status-filter {
            display: flex;
            flex-direction: column;
        }

        .filter-sidebar .priority-filter label, .filter-sidebar .status-filter label {
            margin-bottom: 5px;
        }

        .container {
            margin-left: 5px; /* Dodanie miejsca na wąski pasek */
        }

        table {
            width: calc(100% - 40px); /* Dodanie miejsca na marginesy */
            margin-left: 40px; /* Przesunięcie o 10px w prawo */
        }

    </style>
    <script src="../assets/js/navigation.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.querySelector('.filter-sidebar');
            const toggleBtn = document.querySelector('.filter-sidebar .toggle-btn');

            const toggleSidebar = () => {
                sidebar.classList.toggle('open');
                toggleBtn.classList.toggle('open');
                toggleBtn.classList.toggle('closed');
            };

            toggleBtn.addEventListener('click', toggleSidebar);

            document.addEventListener('click', (event) => {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('open');
                    toggleBtn.classList.add('closed');
                    toggleBtn.classList.remove('open');
                }
            });

            document.getElementById('employee-search').addEventListener('input', function() {
                const filter = this.value.toLowerCase();
                const nodes = document.getElementById('employee-list').getElementsByTagName('label');

                for (let i = 0; i < nodes.length; i++) {
                    if (nodes[i].innerText.toLowerCase().includes(filter)) {
                        nodes[i].style.display = 'block';
                    } else {
                        nodes[i].style.display = 'none';
                    }
                }
            });
        });
    </script>
</head>
<body>
    <header>
        <h1>Zgłoszenia - Sekcja Elektryczna</h1>
    </header>
    <?php include '../sections/includes/navigation.php'; ?>

    <div class="filter-sidebar">
        <button class="toggle-btn closed"></button>
        <h2>Filtry i Sortowanie</h2>
        <form action="" method="get">
            <div class="filter-section">
                <h3>Szukaj pracownika</h3>
                <input type="text" id="employee-search" placeholder="Szukaj pracownika">
                <div class="employee-list" id="employee-list">
                    <?php
                    $users_sql = "SELECT users.id, CONCAT(users.first_name, ' ', users.last_name) as full_name 
                                FROM users 
                                JOIN user_permissions ON users.id = user_permissions.user_id 
                                JOIN permissions ON user_permissions.permission_id = permissions.id 
                                WHERE permissions.name = 'Sekcja Elektryczna'";
                    $users_result = $conn->query($users_sql);
                    if ($users_result->num_rows > 0):
                        while($user = $users_result->fetch_assoc()): ?>
                            <label>
                                <input type="checkbox" name="employees[]" value="<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo (isset($_GET['employees']) && in_array($user['id'], $_GET['employees'])) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </label>
                        <?php endwhile;
                    else: ?>
                        <p>Brak pracowników</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="filter-section">
                <h3>Wybierz kategorie</h3>
                <div class="category-list" id="category-list">
                    <?php foreach ($categories as $category): ?>
                        <label>
                            <input type="checkbox" class="category-checkbox" name="categories[]" value="<?php echo urlencode($category); ?>" <?php echo (in_array($category, $selected_categories)) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($category); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="filter-section priority-filter">
                <h3>Filtruj według priorytetu</h3>
                <label>
                    <input type="checkbox" name="priorities[]" value="niski" <?php echo (in_array('niski', $selected_priorities)) ? 'checked' : ''; ?>>
                    Niski
                </label>
                <label>
                    <input type="checkbox" name="priorities[]" value="średni" <?php echo (in_array('średni', $selected_priorities)) ? 'checked' : ''; ?>>
                    Średni
                </label>
                <label>
                    <input type="checkbox" name="priorities[]" value="wysoki" <?php echo (in_array('wysoki', $selected_priorities)) ? 'checked' : ''; ?>>
                    Wysoki
                </label>
            </div>
            <div class="filter-section status-filter">
                <h3>Filtruj według statusu</h3>
                <label>
                    <input type="checkbox" name="status[]" value="nowe" <?php echo (in_array('nowe', $status_filter)) ? 'checked' : ''; ?>>
                    Nowe
                </label>
                <label>
                    <input type="checkbox" name="status[]" value="w trakcie" <?php echo (in_array('w trakcie', $status_filter)) ? 'checked' : ''; ?>>
                    W trakcie
                </label>
                <label>
                    <input type="checkbox" name="status[]" value="zakończone" <?php echo (in_array('zakończone', $status_filter)) ? 'checked' : ''; ?>>
                    Zakończone
                </label>
            </div>
            <div class="filter-section sort-filter">
                <h3>Sortuj według priorytetu</h3>
                <label>
                    <input type="radio" name="sort" value="priority" <?php echo ($sort_column == 'priority' && $sort_order == 'ASC') ? 'checked' : ''; ?> onclick="this.form.order.value='ASC';">
                    Rosnąco
                </label>
                <label>
                    <input type="radio" name="sort" value="priority" <?php echo ($sort_column == 'priority' && $sort_order == 'DESC') ? 'checked' : ''; ?> onclick="this.form.order.value='DESC';">
                    Malejąco
                </label>
                <input type="hidden" name="order" value="<?php echo htmlspecialchars($sort_order, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="filter-submit">
                <button type="submit">Szukaj</button>
                <a href="?" class="filter-button" style="margin-left: 10px;">Resetuj</a>
            </div>
        </form>
    </div>

    <div class="container">
        <h2>Zgłoszenia - Sekcja Elektryczna</h2>

        <table>
            <tr>
                <th>Priorytet</th>
                <th>Imię i Nazwisko</th>
                <th>Kategoria</th>
                <th>Nazwa oddziału</th>
                <th>Data zgłoszenia</th>
                <th>Data zakończenia</th>
                <th>Pracownik</th>
                <th>Akcje</th>
                <th>Szczegóły</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php
                    $created_at = new DateTime($row["created_at"]);
                    $completed_at = isset($row["completed_at"]) ? new DateTime($row["completed_at"]) : null;
                    ?>
                    <tr>
                        <td class='priority-cell'><span class='priority-circle priority-<?php echo strtolower(htmlspecialchars($row["priority"], ENT_QUOTES, 'UTF-8')); ?>'></span></td>
                        <td><?php echo htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row["category"], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row["department"], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($created_at->format('Y-m-d H:i'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($completed_at ? $completed_at->format('Y-m-d H:i') : 'Nie zakończono', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row["employee_name"] ? $row["employee_name"] : "Nie przypisano", ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class='actions'>
                            <form action='../tickets/assign_employee.php' method='post'>
                                <input type='hidden' name='ticket_id' value='<?php echo htmlspecialchars($row["id"], ENT_QUOTES, 'UTF-8'); ?>'>
                                <select name='employee_id'>
                                    <option value=''>Wybierz pracownika</option>
                                    <?php
                                    $users_sql = "SELECT users.id, CONCAT(users.first_name, ' ', users.last_name) as full_name 
                                                FROM users 
                                                JOIN user_permissions ON users.id = user_permissions.user_id 
                                                JOIN permissions ON user_permissions.permission_id = permissions.id 
                                                WHERE permissions.name = 'Sekcja Elektryczna'";
                                    $users_result = $conn->query($users_sql);
                                    if ($users_result->num_rows > 0):
                                        while($user = $users_result->fetch_assoc()): ?>
                                            <option value='<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>'><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endwhile;
                                    else: ?>
                                        <option value=''>Brak pracowników</option>
                                    <?php endif; ?>
                                </select>
                                <button type='submit'>Przypisz</button>
                            </form>
                        </td>
                        <td class='details'><a href='../tickets/ticket_details.php?id=<?php echo htmlspecialchars($row["id"], ENT_QUOTES, 'UTF-8'); ?>' class='details-button'>Przejdź</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan='9'>Brak zgłoszeń</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
<?php
$conn->close();
?>
