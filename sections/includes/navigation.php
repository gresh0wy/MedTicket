<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Menu</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            overflow-x: hidden;
            
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #3b7dd8;
            padding: 10px 20px;
            box-sizing: border-box;
            width: 100%;
        }   

        .logo, .nav-links {
            display: flex;
            align-items: center;
        }

        .logo {
            margin-left: 40px; /* Zwiększenie dystansu logo od lewej strony */
        }

        .logo img {
            height: 40px;
            margin-right: 20px;
        }

        .nav-links {
            gap: 30px; /* Zwiększenie odstępu między przyciskami */
            margin-left: 40px; /* Odstęp między logo a linkami */
        }

        nav a {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: normal; /* Usunięcie pogrubienia */
        }

        nav a:hover {
            text-decoration: none; /* Usunięcie podkreślenia po najechaniu */
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
            text-align: left;
            padding: 5px 0;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
        }

        .dropdown-content a {
            color: black;
            padding: 10px 20px;
            text-decoration: none;
            display: block;
            border-radius: 8px;
            white-space: nowrap;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .user-menu {
            display: flex;
            align-items: center;
            position: relative;
            margin-right: 20px; /* Większy dystans nazwy użytkownika od prawej strony */
        }

        .user-name {
            cursor: pointer;
            font-weight: normal; /* Usunięcie pogrubienia */
            color: white;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .user-name::after {
            content: ' ▼';
            font-size: 0.8em;
        }

        .user-menu .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            top: 100%;
            padding: 5px 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .menu-toggle span {
            height: 3px;
            width: 25px;
            background: white;
            margin: 4px;
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px;
            }

            .nav-links {
                display: none;
                flex-direction: column;
                width: 100%;
            }

            .nav-links a, .user-menu {
                margin: 10px 0;
                width: 100%;
                text-align: left;
            }

            .nav-links .dropdown-content, .user-menu .dropdown-content {
                position: relative;
                box-shadow: none;
                background-color: #f9f9f9;
            }

            .menu-toggle {
                display: flex;
            }

            .nav-links.show {
                display: flex;
            }
        }
    </style>
</head>
<body>

<nav>
    
        
        <div class="nav-links">
            <a href="../index.php"><span class="material-icons">home</span> Strona główna</a>
            <a href="../tickets/check_status.php"><span class="material-icons">confirmation_number</span> Sprawdź status zgłoszenia</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $permissions = $_SESSION['permissions'] ?? [];
                if (in_array('Super Administrator', $permissions) || in_array('Administrator', $permissions)): ?>
                    <div class="dropdown">
                        <a href="#" class="admin-link"><span class="material-icons">settings</span> Administracja</a>
                        <div class="dropdown-content admin-dropdown">
                            <a href="../admin/users.php"><span class="material-icons">manage_accounts</span> Zarządzanie użytkownikami</a>
                            <a href="../admin/admin_panel.php"><span class="material-icons">announcement</span> Dodaj Komunikat</a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (in_array('Obserwator', $permissions)): ?>
                    <div class="dropdown">
                        <a href="#" class="orders-link"><span class="material-icons">view_list</span> Lista sekcji</a>
                        <div class="dropdown-content orders-dropdown">
                            <a href="../sections/informatyczna.php"><span class="material-icons">computer</span> Sekcja informatyczna</a>
                            <a href="../sections/elektryczna.php"><span class="material-icons">bolt</span> Sekcja elektryczna</a>
                            <a href="../sections/cyber.php"><span class="material-icons">security</span> Cyberbezpieczeństwo</a>
                            <a href="../sections/budowlana.php"><span class="material-icons">apartment</span> Sekcja budowlana</a>
                            <a href="../sections/aparatura.php"><span class="material-icons">medical_services</span> Aparatura medyczna</a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (in_array('Sekcja Elektryczna', $permissions)): ?>
                    <a href="../sections/elektryczna.php"><span class="material-icons">bolt</span> Zlecenia sekcji elektrycznej</a>
                <?php endif; ?>
                <?php if (in_array('Sekcja Budowlana', $permissions)): ?>
                    <a href="../sections/budowlana.php"><span class="material-icons">apartment</span> Zlecenia sekcji budowlanej</a>
                <?php endif; ?>
                <?php if (in_array('Sekcja Informatyczna', $permissions)): ?>
                    <a href="../sections/informatyczna.php"><span class="material-icons">computer</span> Zlecenia sekcji informatycznej</a>
                <?php endif; ?>
                <?php if (in_array('Cyberbezpieczenstwo', $permissions)): ?>
                    <a href="../sections/cyber.php"><span class="material-icons">security</span> Zlecenia sekcji cyberbezpieczeństwa</a>
                <?php endif; ?>
                <?php if (in_array('Aparatura Medyczna', $permissions)): ?>
                    <a href="../sections/aparatura.php"><span class="material-icons">medical_services</span> Zlecenia sekcji aparatury medycznej</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="user-menu">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="user-name"><span class="material-icons">person</span> <?php echo $_SESSION['full_name']; ?></span>
            <div class="dropdown-content">
                <a href="../employee_panel_active.php"><span class="material-icons">assignment</span> Moje zlecenia</a>
                <a href="../change_password.php"><span class="material-icons">lock</span> Zmień hasło</a>
                <a href="../logout.php"><span class="material-icons">logout</span> Wyloguj się</a>
            </div>
        <?php else: ?>
            <a href="login.php"><span class="material-icons">login</span> Zaloguj się</a>
        <?php endif; ?>
    </div>
</nav>



</body>
</html>
