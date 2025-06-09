<?php
session_start();
require_once './includes/db.php'; // dodaj ten wiersz — ścieżka do połączenia z bazą danych
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $https_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $https_url);
    exit;
}

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedTicket - Strona główna</title>
    <link rel="stylesheet" href="assets/css/MainPageStyle.css">
</head>
<body>
    <header>
        <h1>MedTicket</h1>
        <img src="./assets/img/logo-szpital.png" alt="" class="logo-hospital">
    </header>
     <?php include 'includes/navigation.php'; ?>
    <?php
    if (file_exists('data/message.txt')) {
    $message = file_get_contents('data/message.txt');
    if (!empty($message)) {
        echo "<div class='alert'>$message</div>";
    }
}
?>
    <div class="container">
        <h2>Zgłoś problem</h2>
       <form action="tickets/submit_ticket.php" method="post" novalidate>

        <label for="name">Imię i Nazwisko:</label>
        <input type="text" id="name" name="name" required>
        <div id="name-error" class="error-message" style="display: none; color: red;">Proszę wypełnić to pole.</div>
        <label for="section">Dział:</label>
        <select id="section" name="section" required>
        <option value="">Wybierz</option>
        <option value="informatyczna">Dział Informatyki</option>
    <!-- <option value="elektryczna">Sekcja elektryczna</option> -->
                <!-- <option value="cyber">Cyberbezpieczeństwo</option> -->
                <!-- <option value="budowlana">Sekcja budowlana</option> -->
                <!-- <option value="aparatura">Aparatura medyczna</option> -->
        </select>
        <div id="section-error" class="error-message" style="display: none; color: red;">Proszę wybrać sekcję.</div>
        <label for="category">Kategoria zgłoszenia:</label>
        <select id="category" name="category" required>
        <option value="">Wybierz</option>
        </select>
             <div id="category-error" class="error-message" style="display: none; color: red;">Proszę wybrać kategorię.</div>
            <label for="department">Nazwa oddziału/działu:</label>
            <div class="dropdown1">
            <input type="text" id="department-search" placeholder="Wyszukaj oddział/dział">
            <div id="department-list" class="dropdown-content1"></div>
            </div>
            <input type="hidden" id="department" name="department" required>
            <div id="department-error" class="error-message" style="display: none; color: red;">Proszę wybrać oddział/dział.</div>
            <label for="contact_number">Numer wewnętrzny:</label>
            <input type="text" id="contact_number" name="contact_number" required>
            <div id="contact_number-error" class="error-message" style="display: none; color: red;">Proszę podać numer wewnętrzny.</div>
            <label for="subject">Temat:</label>
            <input
                type="text"
                id="subject"
                name="subject"
                required
                maxlength="150"
                style="display: block; margin-bottom: 5px;">
            <small id="subject-counter" style="display: block; margin-bottom: 5px; color: #555;">
            0 / 150 znaków
            </small>
            <div
                id="subject-error"
                class="error-message"
                style="display: none; color: red; margin-bottom: 10px;">  
            </div>

            <label for="description">Opis problemu:</label>
            <textarea id="description" name="description" rows="4" required maxlength="500"></textarea>
            <small id="description-counter" style="display:block; margin-bottom:5px; color:#555;">0 / 500 znaków</small>
            <div id="description-error" class="error-message" style="display: none; color: red;">Proszę opisać problem.</div>


            
            <label for="priority">Stopień priorytetu:</label>
            <select id="priority" name="priority" required>
                <option value="">Wybierz</option>
                <option value="niski">Niski</option>
                <option value="średni">Średni</option>
                <option value="Wysoki">Wysoki</option>
                <option value="Krytyczny">Krytyczny</option>
            </select>
            
            <label for="recurrence">Powtarzalność problemu:</label>
            <select id="recurrence" name="recurrence" required>
                <option value="">Wybierz</option>
                <option value="jednorazowy">Jednorazowy</option>
                <option value="sporadyczny">Sporadyczny</option>
                <option value="częsty">Częsty</option>
            </select>
            <?php
                $show_select = false;
                if (isset($_SESSION['user_id'])) {
                $current_user_id = $_SESSION['user_id'];

    $permission_check_sql = "SELECT permissions.name 
                             FROM user_permissions 
                             JOIN permissions ON user_permissions.permission_id = permissions.id 
                             WHERE user_permissions.user_id = ? AND permissions.name = 'Sekcja Informatyczna'";
    
    $stmt = $conn->prepare($permission_check_sql);
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $show_select = true;
    }
}
?>

<?php if ($show_select): ?>
    <label for="assigned_user">Przypisz do użytkownika:</label>
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
<?php endif; ?>


            <button type="submit">Wyślij zgłoszenie</button>
        </form>
    </div>
     <?php include 'includes/footer.php'; ?>
   



    <script src="assets/js/navigation.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/form-validation.js"></script>
</body>
</html>
