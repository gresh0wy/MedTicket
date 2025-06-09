<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Funkcja pomocnicza do pobierania liczby zgłoszeń wg statusu
function get_ticket_count($conn, $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tickets WHERE status = ? AND section = 'informatyczna'");
    $stmt->bind_param('s', $status);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

$new_tickets_count = get_ticket_count($conn, 'nowe');
$open_tickets_count = get_ticket_count($conn, 'w trakcie');
$reviewed_tickets_count = get_ticket_count($conn, 'zakończone');
$rejected_tickets_count = get_ticket_count($conn, 'odrzucono');

// Pobierz uprawnienia użytkownika
$permissions = [];
$stmt = $conn->prepare("SELECT p.name FROM user_permissions up JOIN permissions p ON up.permission_id = p.id WHERE up.user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $permissions[] = $row['name'];
}

if (!in_array('Obserwator', $permissions) && !in_array('Sekcja Informatyczna', $permissions)) {
    header("Location: ../login.php");
    exit();
}

// Funkcja pomocnicza do pobierania unikalnych wartości
function get_unique_values($conn, $column) {
    $values = [];
    $sql = "SELECT DISTINCT $column FROM tickets WHERE section = 'informatyczna'";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $values[] = $row[$column];
    }
    return $values;
}

$categories = get_unique_values($conn, 'category');
$departments = get_unique_values($conn, 'department');

// Parametry filtrów
$filter = $_GET['filter'] ?? 'all';
$sort_column = $_GET['sort'] ?? 'created_at';
$sort_order = strtoupper($_GET['order'] ?? 'DESC');
$priority_filter = $_GET['priority'] ?? [];
$status_filter_button = $_GET['status_filter'] ?? '';
$status_filter = $_GET['status'] ?? [];
if ($status_filter_button) $status_filter = [$status_filter_button];
$search = $_GET['search'] ?? '';
$selected_employees = $_GET['employees'] ?? [];
$selected_categories = array_map('urldecode', $_GET['categories'] ?? []);
$selected_department = urldecode($_GET['department'] ?? '');
$start_date = $_GET['start_date'] ?? '';
$start_time = $_GET['start_time'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$end_time = $_GET['end_time'] ?? '';

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 100;
$offset = ($page - 1) * $per_page;

// Budowa zapytania SQL
$sql = "SELECT SQL_CALC_FOUND_ROWS tickets.*, CONCAT(users.first_name, ' ', users.last_name) as employee_name FROM tickets 
        LEFT JOIN users ON tickets.employee_id = users.id WHERE tickets.section = 'informatyczna'";

if (!empty($status_filter)) {
    $safe = array_map([$conn, 'real_escape_string'], $status_filter);
    $sql .= " AND tickets.status IN ('" . implode("','", $safe) . "')";
}

if (!empty($priority_filter)) {
    $safe = array_map([$conn, 'real_escape_string'], $priority_filter);
    $sql .= " AND tickets.priority IN ('" . implode("','", $safe) . "')";
}

if (!empty($selected_categories)) {
    $safe = array_map([$conn, 'real_escape_string'], $selected_categories);
    $sql .= " AND tickets.category IN ('" . implode("','", $safe) . "')";
}

if ($selected_department) {
    $sql .= " AND tickets.department = '" . $conn->real_escape_string($selected_department) . "'";
}

if ($search) {
    $s = $conn->real_escape_string($search);
    $sql .= " AND (tickets.id = '$s' OR tickets.name LIKE '%$s%' OR CONCAT(users.first_name, ' ', users.last_name) LIKE '%$s%' OR tickets.department LIKE '%$s%')";
}

if ($start_date) {
    $sql .= " AND DATE(tickets.created_at) >= '" . $conn->real_escape_string($start_date) . "'";
    if ($start_time) {
        $sql .= " AND TIME(tickets.created_at) >= '" . $conn->real_escape_string($start_time) . "'";
    }
}

if ($end_date) {
    $sql .= " AND DATE(tickets.created_at) <= '" . $conn->real_escape_string($end_date) . "'";
    if ($end_time) {
        $sql .= " AND TIME(tickets.created_at) <= '" . $conn->real_escape_string($end_time) . "'";
    }
}

if (!empty($selected_employees)) {
    $employee_ids = implode(',', array_map('intval', $selected_employees));
    $sql .= " AND tickets.employee_id IN ($employee_ids)";
}

$sql .= " ORDER BY $sort_column $sort_order LIMIT $per_page OFFSET $offset";
$result = $conn->query($sql);

if ($conn->error) {
    echo "Błąd: " . $conn->error;
    exit();
}

$total_rows = $conn->query("SELECT FOUND_ROWS() as total")->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);
$total_tickets_count = $conn->query("SELECT COUNT(*) as count FROM tickets WHERE section = 'informatyczna'")->fetch_assoc()['count'];

function get_sort_link($column, $current_column, $current_order) {
    $order = ($current_column == $column && $current_order == 'ASC') ? 'DESC' : 'ASC';
    return "?" . http_build_query(array_merge($_GET, ['sort' => $column, 'order' => $order]));
}

function get_sort_icon($column, $current_column, $current_order) {
    return $current_column == $column ? ($current_order == 'ASC' ? '▲' : '▼') : '▲';
}

function shorten_department_name($name, $limit = 9) {
    $words = explode(' ', $name);
    return count($words) > $limit ? implode(' ', array_slice($words, 0, $limit)) . '...' : $name;
}
?>




    <!DOCTYPE html>
    <html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Zgłoszenia - Sekcja Informatyczna</title>
        <link rel="stylesheet" href="../assets/css/section.css">
        <style>
       
</style>
    </head>
    <body>
       
        <?php include '../sections/includes/navigation.php'; ?>
        <header>
            <h1>Zgłoszenia - Sekcja Informatyczna</h1>
        </header>
        <div class="container">
            <?php 
            if (isset($_GET['status_filter'])) {
                switch ($_GET['status_filter']) {
                    case 'nowe':
                        $header_text = "Nowe zgłoszenia";
                        break;
                    case 'w trakcie':
                        $header_text = "Otwarte zgłoszenia";
                        break;
                    case 'zakończone':
                        $header_text = "Zrealizowane zgłoszenia";
                        break;
                    case 'odrzucono':
                        $header_text = "Odrzucone zgłoszenia";
                        break;
                    default:
                        $header_text = "Wszystkie zgłoszenia";
                        break;
                }
            } else {
                $header_text = "Wszystkie zgłoszenia";
            }
            ?>
            <div class="dynamic-header"><?php echo $header_text; ?></div>

         <div class="buttons-container">
  <!-- Wszystkie zgłoszenia -->
  <a
    href="?"
    class="filter-button"
    data-status="all"
  >
    <div class="icon-square">
      <img src="../assets/icons/all-tickets.svg" alt="Wszystkie zgłoszenia" />
    </div>
    <div class="text-container">
      <div>Wszystkie zgłoszenia</div>
      <div class="count">(<?php echo $total_tickets_count; ?>)</div>
    </div>
  </a>

  <!-- Nowe zgłoszenia -->
  <a
    href="?<?php echo http_build_query(array_merge($_GET, ['status_filter' => 'nowe'])); ?>"
    class="filter-button"
    data-status="nowe"
  >
    <div class="icon-square">
      <img src="../assets/icons/new-tickets.svg" alt="Nowe zgłoszenia" />
    </div>
    <div class="text-container">
      <div>Nowe zgłoszenia</div>
      <div class="count">(<?php echo $new_tickets_count; ?>)</div>
    </div>
  </a>

  <!-- W trakcie -->
  <a
    href="?<?php echo http_build_query(array_merge($_GET, ['status_filter' => 'w trakcie'])); ?>"
    class="filter-button"
    data-status="w trakcie"
  >
    <div class="icon-square">
      <img src="../assets/icons/open-tickets.svg" alt="Otwarte zgłoszenia" />
    </div>
    <div class="text-container">
      <div>Otwarte zgłoszenia</div>
      <div class="count">(<?php echo $open_tickets_count; ?>)</div>
    </div>
  </a>

  <!-- Zakończone -->
  <a
    href="?<?php echo http_build_query(array_merge($_GET, ['status_filter' => 'zakończone'])); ?>"
    class="filter-button"
    data-status="zakończone"
  >
    <div class="icon-square">
      <img src="../assets/icons/reviewed-tickets.svg" alt="Zrealizowane zgłoszenia" />
    </div>
    <div class="text-container">
      <div>Zrealizowane zgłoszenia</div>
      <div class="count">(<?php echo $reviewed_tickets_count; ?>)</div>
    </div>
  </a>

  <!-- Odrzucone -->
  <a
    href="?<?php echo http_build_query(array_merge($_GET, ['status_filter' => 'odrzucono'])); ?>"
    class="filter-button"
    data-status="odrzucono"
  >
    <div class="icon-square">
      <img src="../assets/icons/bin.svg" alt="Odrzucone zgłoszenia" />
    </div>
    <div class="text-container">
      <div>Odrzucone zgłoszenia</div>
      <div class="count">(<?php echo $rejected_tickets_count; ?>)</div>
    </div>
  </a>
</div>


    <!-- Wyszukiwarka i resetowanie filtrów -->
    <div class="search-form">
        <form action="" method="get" style="display: inline-block; margin-right: 10px;">
            <input type="text" name="search" placeholder="Szukaj" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_column, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars($sort_order, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit">Szukaj</button>
        </form>

        <form action="" method="get" style="display: inline-block;">
            <?php foreach ($_GET as $key => $value): ?>
                <?php if (in_array($key, ['status_filter'])): // Zachowaj tylko filtr statusu ?>
                    <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <button type="submit" style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Resetuj filtry</button>
        </form>
    </div>


            <table >
                <tr>
                    <th class="id-cell">
                        <div class="icon-sort-container">
                            <a href="<?php echo get_sort_link('id', $sort_column, $sort_order); ?>">ID</a>
                            
                        </div>
                    </th>
                    <th>
                        <div class="icon-sort-container priority-header">
                            <a href="<?php echo get_sort_link('priority', $sort_column, $sort_order); ?>">Priorytet</a>
                            <img src="../assets/icons/tune.svg" alt="Tune Icon" class="priority-icon" />
                            <div class="priority-dropdown">
        <form method="GET" action="">
            <?php foreach ($_GET as $key => $value): ?>
                <?php if (!in_array($key, ['priority'])): ?>
                    <?php if (is_array($value)): ?>
                        <?php foreach ($value as $v): ?>
                            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>[]" value="<?php echo htmlspecialchars($v); ?>">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <label><input type="checkbox" name="priority[]" value="niski"> Niski</label>
            <label><input type="checkbox" name="priority[]" value="średni"> Średni</label>
            <label><input type="checkbox" name="priority[]" value="wysoki"> Wysoki</label>
            <button type="submit">Zastosuj</button>
        </form>
    </div>

                        </div>
                    </th>
                    <th>
                        <div class="icon-sort-container category-header">
                            <a href="<?php echo get_sort_link('category', $sort_column, $sort_order); ?>">Kategoria</a>
                            <img src="../assets/icons/tune.svg" alt="Tune Icon" class="category-icon" />
                            <div class="category-dropdown">
                            <form method="GET" action="">
        <?php foreach ($_GET as $key => $value): ?>
            <?php if (!in_array($key, ['categories'])): // Pomijamy bieżący filtr kategorii ?>
                <?php if (is_array($value)): ?>
                    <?php foreach ($value as $v): ?>
                        <input type="hidden" name="<?php echo htmlspecialchars($key); ?>[]" value="<?php echo htmlspecialchars($v); ?>">
                    <?php endforeach; ?>
                <?php else: ?>
                    <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php foreach ($categories as $category): ?>
            <label><input type="checkbox" name="categories[]" value="<?php echo urlencode($category); ?>" <?php echo in_array($category, $selected_categories) ? 'checked' : ''; ?>> <?php echo htmlspecialchars($category); ?></label>
        <?php endforeach; ?>
        <button type="submit">Zastosuj</button>
    </form>

                            </div>
                        </div>
                    </th>
                    <th>
                        <div class="icon-sort-container">
                            <a href="<?php echo get_sort_link('department', $sort_column, $sort_order); ?>">Nazwa oddziału</a> 
                            <img src="../assets/icons/sort.svg " alt="Sort Icon" />
                        </div>
                    </th>
                    <th>
                        <div class="icon-sort-container date-header">
                            <a href="<?php echo get_sort_link('created_at', $sort_column, $sort_order); ?>">Data zgłoszenia</a>
                            <img src="../assets/icons/tune.svg" alt="Tune Icon" class="date-icon" />
                            <div class="date-dropdown">
                                <form method="GET" action="">
                                    <?php foreach ($_GET as $key => $value): ?>
                                        <?php if (!in_array($key, ['start_date', 'end_date', 'start_time', 'end_time'])): ?>
                                            <?php if (is_array($value)): ?>
                                                <?php foreach ($value as $v): ?>
                                                    <input type="hidden" name="<?php echo htmlspecialchars($key); ?>[]" value="<?php echo htmlspecialchars($v); ?>">
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                    <label for="start_date">Od daty:</label>
                                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" />

                                    <label for="start_time">Od godziny:</label>
                                    <input type="time" name="start_time" value="<?php echo htmlspecialchars($start_time); ?>" />

                                    <label for="end_date">Do daty:</label>
                                    
                                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" />

                                    <label for="end_time">Do godziny:</label>
                                    <input type="time" name="end_time" value="<?php echo htmlspecialchars($end_time); ?>" />

                                    <button type="submit">Zastosuj</button>
                                </form>
                            </div>
                        </div>
                    </th>
                    <?php if (isset($_GET['status_filter']) && $_GET['status_filter'] == 'zakończone'): ?>
                    <th>
                        <div class="icon-sort-container date-header">
                            <a href="<?php echo get_sort_link('completed_at', $sort_column, $sort_order); ?>">Data zakończenia</a>
                        </div>
                    </th>
                    <?php endif; ?>
                    <th class="subject">Temat zgłoszenia</th>
                    <th>Pracownik</th>
                    <th>Akcje</th>
                    <th>Szczegóły</th>
                </tr>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <?php
                        $created_at = new DateTime($row["created_at"]);
                        $completed_at = $row["completed_at"] ? new DateTime($row["completed_at"]) : null;
                        ?>
                        <tr>
                            <td class="id-cell id-column"><?php echo htmlspecialchars($row["id"], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="priority-column" style="text-align: center; vertical-align: middle;">
                            <span class="priority-cell priority-<?php echo strtolower(htmlspecialchars($row["priority"], ENT_QUOTES, 'UTF-8')); ?>">
                            <?php echo htmlspecialchars(ucfirst($row["priority"]), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            </td>

                            <td class="category-column"><?php echo htmlspecialchars($row["category"], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="department-column"><?php echo htmlspecialchars(shorten_department_name($row["department"]), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="date-column"><?php echo htmlspecialchars($created_at->format('Y-m-d H:i'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <?php if (isset($_GET['status_filter']) && $_GET['status_filter'] == 'zakończone'): ?>
                            <td class="date-column"><?php echo $completed_at ? htmlspecialchars($completed_at->format('Y-m-d H:i'), ENT_QUOTES, 'UTF-8') : 'Nie zakończono'; ?></td>
                            <?php endif; ?>
                            <td class="subject"><?php echo htmlspecialchars($row["subject"], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="employee-column"><?php echo htmlspecialchars($row["employee_name"] ? $row["employee_name"] : "Nie przypisano", ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class='actions'>
    <form action='../tickets/assign_employee.php' method='post' class="assign-employee-form">
        <input type='hidden' name='ticket_id' value='<?php echo htmlspecialchars($row["id"], ENT_QUOTES, 'UTF-8'); ?>'>
        <select name='employee_id' onchange="assignEmployee(this)">
            <option value=''>Wybierz pracownika</option>
            <?php
            $users_sql = "SELECT users.id, CONCAT(users.first_name, ' ', users.last_name) as full_name 
                          FROM users 
                          JOIN user_permissions ON users.id = user_permissions.user_id 
                          JOIN permissions ON user_permissions.permission_id = permissions.id 
                          WHERE permissions.name = 'Sekcja Informatyczna'";

            $users_result = $conn->query($users_sql);
            if ($users_result->num_rows > 0) {
                while ($user_row = $users_result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($user_row['id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($user_row['full_name'], ENT_QUOTES, 'UTF-8') . "</option>";
                }
            } else {
                echo "<option value=''>Brak dostępnych pracowników</option>";
            }
            ?>
        </select>
    </form>
</td>



                            <td class='details'><a href='../tickets/ticket_details.php?id=<?php echo htmlspecialchars($row["id"], ENT_QUOTES, 'UTF-8'); ?>' class='details-button'>Przejdź</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan='10'>Brak zgłoszeń</td></tr>
                <?php endif; ?>
            </table>

            <!-- Paginacja -->
            <div class="pagination">
                <?php
                $range = 4; // Number of pages to show before and after the current page
                $start = max(1, $page - $range);
                $end = min($total_pages, $page + $range);

                if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1, 'sort' => $sort_column, 'order' => $sort_order])); ?>">&laquo; Poprzednia</a>
                <?php endif;

                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i, 'sort' => $sort_column, 'order' => $sort_order])); ?>"<?php if ($i == $page) echo ' class="active"'; ?>><?php echo $i; ?></a>
                <?php endfor;

                if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1, 'sort' => $sort_column, 'order' => $sort_order])); ?>">Następna &raquo;</a>
                <?php endif; ?>

                <form action="" method="get" style="display: inline-block; margin-left: 10px;">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_column, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="order" value="<?php echo htmlspecialchars($sort_order, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php
                    // Keep all current GET parameters except 'page'
                    foreach ($_GET as $key => $value) {
                        if ($key != 'page') {
                            if (is_array($value)) {
                                foreach ($value as $v) {
                                    echo '<input type="hidden" name="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '[]" value="' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '">';
                                }
                            } else {
                                echo '<input type="hidden" name="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">';
                            }
                        }
                    }
                    ?>
                    <input type="number" name="page" min="1" max="<?php echo $total_pages; ?>" placeholder="Strona" style="width: 60px; padding: 8px; border: 1px solid #ddd; border-radius: 5px; margin-right: 10px;">
                    <button type="submit" style="padding: 8px 16px; border: 1px solid #ddd; background-color: #007bff; color: white; border-radius: 5px; cursor: pointer;">Idź</button>
                </form>
            </div>

        </div>
        <script>
function assignEmployee(selectElement) {
    const form = selectElement.closest('form');
    const ticketId = form.querySelector("input[name='ticket_id']").value;
    const employeeId = selectElement.value;

    if (employeeId === '') {
        return; // Jeśli nie wybrano pracownika, zakończ
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../tickets/assign_employee.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            if (xhr.responseText === 'success') {
                // Odśwież stronę po sukcesie
                location.reload(); // Możesz również odświeżyć konkretny wiersz w tabeli zamiast całej strony
            } else {
                console.log('Błąd przypisania pracownika: ' + xhr.responseText);
            }
        }
    };
    xhr.send(`ticket_id=${ticketId}&employee_id=${employeeId}`);
}
</script>

<script src="../assets/js/navigation.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Dropdowns
        const priorityIcon = document.querySelector(".priority-icon");
        const categoryIcon = document.querySelector(".category-icon");
        const dateIcon = document.querySelector(".date-icon");

        const priorityDropdown = document.querySelector(".priority-dropdown");
        const categoryDropdown = document.querySelector(".category-dropdown");
        const dateDropdown = document.querySelector(".date-dropdown");

        priorityIcon.addEventListener("click", function(event) {
            event.stopPropagation();
            priorityDropdown.style.display = priorityDropdown.style.display === "block" ? "none" : "block";
            categoryDropdown.style.display = "none";
            dateDropdown.style.display = "none";
        });

        categoryIcon.addEventListener("click", function(event) {
            event.stopPropagation();
            categoryDropdown.style.display = categoryDropdown.style.display === "block" ? "none" : "block";
            priorityDropdown.style.display = "none";
            dateDropdown.style.display = "none";
        });

        dateIcon.addEventListener("click", function(event) {
            event.stopPropagation();
            dateDropdown.style.display = dateDropdown.style.display === "block" ? "none" : "block";
            priorityDropdown.style.display = "none";
            categoryDropdown.style.display = "none";
        });

        // Close dropdowns if clicked outside
        document.addEventListener("click", function(event) {
            if (!priorityDropdown.contains(event.target) && event.target !== priorityIcon) {
                priorityDropdown.style.display = "none";
            }
            if (!categoryDropdown.contains(event.target) && event.target !== categoryIcon) {
                categoryDropdown.style.display = "none";
            }
            if (!dateDropdown.contains(event.target) && event.target !== dateIcon) {
                dateDropdown.style.display = "none";
            }
        });
    });
    
</script>


    </body>
    </html>

    <?php
    $conn->close();
    ?>
