<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "przytula2000";
$dbname = "medticket_alpha";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$section = isset($_GET['section']) ? $_GET['section'] : 'all';
$period = isset($_GET['period']) ? $_GET['period'] : 'day';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$refreshInterval = isset($_GET['refreshInterval']) ? $_GET['refreshInterval'] : '30';

function fetch_statistics($conn, $interval, $section, $year = null) {
    $section_condition = $section !== 'all' ? "AND section = '$section'" : '';
    $year_condition = $year ? "AND YEAR(created_at) = '$year'" : '';
    $date_condition = '';

    switch ($interval) {
        case '1 DAY':
            $date_condition = "DATE(created_at) = CURDATE()";
            break;
        case '7 DAY':
            $date_condition = "created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
            break;
        case '1 MONTH':
            $date_condition = "created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            break;
        case '1 YEAR':
            $date_condition = "created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            break;
    }

    $sql = "
        SELECT 
        COUNT(CASE WHEN status = 'Nowe' THEN 1 END) AS new_requests,
        COUNT(CASE WHEN status = 'W trakcie' THEN 1 END) AS in_progress_requests,
        COUNT(CASE WHEN status = 'Zakończone' THEN 1 END) AS completed_requests,
        DATE_FORMAT(created_at, '%Y-%m-%d %H') as date_hour,
        DATE_FORMAT(created_at, '%Y-%m-%d') as date_day,
        DATE_FORMAT(created_at, '%Y-%m') as date_month
        FROM tickets
        WHERE $date_condition $section_condition $year_condition
        GROUP BY date_hour, date_day, date_month
        ORDER BY created_at ASC;
    ";

    $result = $conn->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    } else {
        echo "SQL Error: " . $conn->error;
    }

    return $data;
}

$stats = fetch_statistics($conn, $period == 'day' ? '1 DAY' : ($period == 'week' ? '7 DAY' : ($period == 'month' ? '1 MONTH' : '1 YEAR')), $section, $year);
$periodLabel = $period == 'day' ? 'Dzisiejsze zgłoszenia' : ($period == 'week' ? 'Ostatni tydzień' : ($period == 'month' ? 'Ostatni miesiąc' : 'Rok ' . $year));

$yearsResult = $conn->query("SELECT DISTINCT YEAR(created_at) AS year FROM tickets ORDER BY year DESC");
$years = [];
while ($row = $yearsResult->fetch_assoc()) {
    $years[] = $row['year'];
}

$lastRefreshTime = date('Y-m-d H:i:s');

$conn->close();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statystyki zgłoszeń</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
            width: 100%;
        }

        header {
            background: #00796b;
            color: #fff;
            padding: 20px;
            width: 100%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: center;
        }

        nav {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 1200px;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
            font-size: 16px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            color: #333;
            margin: 20px 0;
            text-align: center;
            font-size: 2em;
        }

        form {
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        form div {
            margin: 10px 0;
        }

        label {
            margin-right: 10px;
            font-size: 1.1em;
        }

        select, button {
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
            background-color: #fff;
        }

        button {
            background-color: #00796b;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #004d40;
        }

        .charts-container {
            display: flex;
            width: 100%;
        }

        .chart-container {
            flex: 1;
            margin: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .toggle-button {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            background-color: #00796b;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        p {
            font-size: 1.1em;
            color: #666;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            form {
                flex-direction: column;
                align-items: flex-start;
            }
            .charts-container {
                flex-direction: column;
            }
            .chart-container {
                max-width: 100%;
            }
        }

        .dropdown, .dropdown1 {
            position: relative;
            display: inline-block;
        }

        .dropdown-content, .dropdown-content1 {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            margin-top: 10px;
        }

        .dropdown-content a, .dropdown-content1 div {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            cursor: pointer;
        }

        .dropdown-content a:hover, .dropdown-content1 div:hover {
            background-color: #f1f1f1;
        }

        .user-menu {
            display: inline-block;
            position: relative;
        }

        .user-name {
            cursor: pointer;
            color: white;
            font-weight: bold;
        }

        .user-name::after {
            content: ' ▼';
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="nav-links">
                <a href="#">Strona główna</a>
                <a href="#">Sprawdź status zgłoszenia</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    $permissions = $_SESSION['permissions'] ?? [];
                    if (in_array('Super Administrator', $permissions) || in_array('Administrator', $permissions)): ?>
                        <div class="dropdown">
                            <a href="#" class="admin-link">Administracja</a>
                            <div class="dropdown-content admin-dropdown">
                                <a href="#">Zarządzanie użytkownikami</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (in_array('Super Administrator', $permissions) || in_array('Administrator', $permissions) || in_array('Obserwator', $permissions)): ?>
                        <div class="dropdown">
                            <a href="#" class="orders-link">Lista zleceń</a>
                            <div class="dropdown-content orders-dropdown">
                                <a href="#">Informatyczna</a>
                                <a href="#">Elektryczna</a>
                                <a href="#">Cyberbezpieczeństwo</a>
                                <a href="#">Budowlana</a>
                                <a href="#">Aparatura medyczna</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (in_array('Sekcja Elektryczna', $permissions)): ?>
                        <a href="#" class="dropdown">Lista zleceń sekcji elektrycznej</a>
                    <?php endif; ?>
                    <?php if (in_array('Sekcja Budowlana', $permissions)): ?>
                        <a href="#" class="dropdown">Lista zleceń sekcji budowlanej</a>
                    <?php endif; ?>
                    <?php if (in_array('Sekcja Informatyczna', $permissions)): ?>
                        <a href="#" class="dropdown">Lista zleceń sekcji informatycznej</a>
                    <?php endif; ?>
                    <?php if (in_array('Cyberbezpieczenstwo', $permissions)): ?>
                        <a href="#" class="dropdown">Lista zleceń sekcji cyberbezpieczeństwa</a>
                    <?php endif; ?>
                    <?php if (in_array('Aparatura Medyczna', $permissions)): ?>
                        <a href="#" class="dropdown">Lista zleceń sekcji aparatury medycznej</a>
                    <?php endif; ?>
                    <div class="user-menu">
                        <span class="user-name"><?php echo $_SESSION['full_name']; ?></span>
                        <div class="dropdown-content">
                            <a href="#">Moje zlecenia</a>
                            <a href="#">Zmień hasło</a>
                            <a href="#">Wyloguj się</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="#">Zaloguj się</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <div class="container">
        <h1>Statystyki zgłoszeń</h1>
        <form id="statsForm" method="GET" action="">
            <div>
                <label for="section">Wybierz sekcję:</label>
                <select id="section" name="section" onchange="document.getElementById('statsForm').submit();">
                    <option value="all" <?php if($section == 'all') echo 'selected'; ?>>Wszystkie</option>
                    <option value="informatyczna" <?php if($section == 'informatyczna') echo 'selected'; ?>>Informatyczna</option>
                    <option value="elektryczna" <?php if($section == 'elektryczna') echo 'selected'; ?>>Elektryczna</option>
                    <option value="budowlana" <?php if($section == 'budowlana') echo 'selected'; ?>>Budowlana</option>
                    <option value="cyberbezpieczenstwo" <?php if($section == 'cyberbezpieczenstwo') echo 'selected'; ?>>Cyberbezpieczeństwo</option>
                    <option value="aparatura_medyczna" <?php if($section == 'aparatura_medyczna') echo 'selected'; ?>>Aparatura medyczna</option>
                </select>
            </div>
            <div>
                <label for="period">Wybierz okres:</label>
                <select id="period" name="period" onchange="document.getElementById('statsForm').submit();">
                    <option value="day" <?php if($period == 'day') echo 'selected'; ?>>Dzień</option>
                    <option value="week" <?php if($period == 'week') echo 'selected'; ?>>Tydzień</option>
                    <option value="month" <?php if($period == 'month') echo 'selected'; ?>>Miesiąc</option>
                    <option value="year" <?php if($period == 'year') echo 'selected'; ?>>Rok</option>
                </select>
            </div>
            <div id="yearSelector" style="display: <?php echo $period == 'year' ? 'block' : 'none'; ?>;">
                <label for="year">Wybierz rok:</label>
                <select id="year" name="year" onchange="document.getElementById('statsForm').submit();">
                    <?php foreach ($years as $yr): ?>
                        <option value="<?php echo $yr; ?>" <?php if($year == $yr) echo 'selected'; ?>><?php echo $yr; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="refreshInterval">Odświeżaj co:</label>
                <select id="refreshInterval" name="refreshInterval" onchange="document.getElementById('statsForm').submit();">
                    <option value="30" <?php if($refreshInterval == '30') echo 'selected'; ?>>30 sekund</option>
                    <option value="60" <?php if($refreshInterval == '60') echo 'selected'; ?>>1 minuta</option>
                    <option value="600" <?php if($refreshInterval == '600') echo 'selected'; ?>>10 minut</option>
                    <option value="3600" <?php if($refreshInterval == '3600') echo 'selected'; ?>>1 godzina</option>
                </select>
            </div>
        </form>
        <h2>Statystyki dla sekcji: <?php echo $section == 'all' ? 'Wszystkie' : ucfirst(str_replace('_', ' ', $section)); ?> (<?php echo $periodLabel; ?>)</h2>
        <div class="charts-container">
            <div class="chart-container">
                <canvas id="lineChart"></canvas>
            </div>
            <div class="chart-container">
                <button class="toggle-button" onclick="toggleChartType()">Przełącz wykres</button>
                <canvas id="barPieChart"></canvas>
            </div>
        </div>
        <p>Odświeżono: <span id="lastRefreshTime"><?php echo $lastRefreshTime; ?></span></p>
    </div>
    <script>
        let refreshInterval = <?php echo $refreshInterval; ?>;
        const lastRefreshTime = new Date('<?php echo $lastRefreshTime; ?>');
        let lastDisplayedMinute = -1; // Przechowuje ostatnio wyświetloną minutę

        let lineChart = null;
        let barPieChart = null;
        let currentChartType = 'bar'; // Domyślny typ wykresu

        function destroyCharts() {
            if (lineChart) {
                lineChart.destroy();
            }
            if (barPieChart) {
                barPieChart.destroy();
            }
        }

        function createCharts() {
            const statsData = {
                labels: ['Nowe zgłoszenia', 'Zgłoszenia w trakcie', 'Zakończone zgłoszenia'],
                datasets: [{
                    label: '<?php echo $periodLabel; ?>',
                    data: [
                        <?php echo array_sum(array_column($stats, 'new_requests')); ?>,
                        <?php echo array_sum(array_column($stats, 'in_progress_requests')); ?>,
                        <?php echo array_sum(array_column($stats, 'completed_requests')); ?>
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)', 
                        'rgba(255, 206, 86, 0.2)', 
                        'rgba(54, 162, 235, 0.2)'  
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)', 
                        'rgba(255, 206, 86, 1)', 
                        'rgba(54, 162, 235, 1)'  
                    ],
                    borderWidth: 1
                }]
            };

            const lineChartData = {
                labels: formatXAxisLabels('<?php echo $period; ?>'),
                datasets: [{
                    label: 'Nowe zgłoszenia',
                    data: formatLineChartData(<?php echo json_encode($stats); ?>, 'new_requests'),
                    fill: true,
                    borderColor: 'orange',
                    backgroundColor: 'rgba(255, 165, 0, 0.2)',
                    pointBackgroundColor: 'orange',
                    pointBorderColor: 'orange',
                    tension: 0.1
                }]
            };

            const lineConfig = {
                type: 'line',
                data: lineChartData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Nowe zgłoszenia'
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: '<?php echo $period === "day" ? "Godzina" : "Data"; ?>'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Liczba nowych zgłoszeń'
                            },
                            ticks: {
                                beginAtZero: true,
                                callback: function(value) {
                                    if (Number.isInteger(value)) {
                                        return value;
                                    }
                                },
                                stepSize: 1 // Ustawienie kroku na 1
                            }
                        }
                    }
                }
            };

            const barPieConfig = {
                type: currentChartType,
                data: statsData,
                options: {
                    scales: currentChartType === 'bar' ? {
                        y: {
                            beginAtZero: true,
                            display: true
                        }
                    } : {}
                }
            };

            lineChart = new Chart(
                document.getElementById('lineChart'),
                lineConfig
            );

            barPieChart = new Chart(
                document.getElementById('barPieChart'),
                barPieConfig
            );
        }

        function toggleChartType() {
            currentChartType = currentChartType === 'bar' ? 'pie' : 'bar';
            destroyCharts();
            createCharts();
        }

        function toggleYearSelector() {
            const period = document.getElementById('period').value;
            const yearSelector = document.getElementById('yearSelector');
            if (period === 'year') {
                yearSelector.style.display = 'block';
            } else {
                yearSelector.style.display = 'none';
            }
        }

        function getTimeDifference(date) {
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffSecs = Math.floor((diffMs % 60000) / 1000);

            if (diffMins > 0) {
                return `${diffMins} minut${diffMins === 1 ? 'ę' : 'y'} temu`;
            } else {
                return `${diffSecs} sekund${diffSecs === 1 ? 'ę' : 'y'} temu`;
            }
        }

        function updateLastRefreshTime() {
            const now = new Date();
            const currentMinute = now.getMinutes();
            if (currentMinute !== lastDisplayedMinute) { // Aktualizuj tylko, gdy zmieni się minuta
                const timeString = getTimeDifference(lastRefreshTime);
                document.getElementById('lastRefreshTime').innerText = timeString;
                lastDisplayedMinute = currentMinute;
            }
        }

        function refreshStatistics() {
            location.reload();
        }

        document.getElementById('period').addEventListener('change', toggleYearSelector);

        toggleYearSelector();
        updateLastRefreshTime();

        setInterval(updateLastRefreshTime, 1000); // Sprawdzaj co sekundę, ale aktualizuj co minutę
        setInterval(refreshStatistics, refreshInterval * 1000);

        function formatXAxisLabels(period) {
            const now = new Date();

            if (period === 'day') {
                const hours = Array.from({ length: now.getHours() + 1 }, (_, i) => (i < 10 ? '0' : '') + i + ':00');
                return hours;
            } else if (period === 'week') {
                const daysOfWeek = ['Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota'];
                const last7Days = [];
                for (let i = 6; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    last7Days.push(daysOfWeek[date.getDay()]);
                }
                return last7Days;
            } else if (period === 'month') {
                const daysInMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
                const days = Array.from({ length: now.getDate() }, (_, i) => (i + 1 < 10 ? '0' : '') + (i + 1));
                return days;
            } else if (period === 'year') {
                const months = ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'];
                return months.slice(0, now.getMonth() + 1);
            }
        }

        function formatLineChartData(stats, key) {
            const period = '<?php echo $period; ?>';
            const now = new Date();

            if (period === 'day') {
                const data = Array(now.getHours() + 1).fill(0);
                stats.forEach(stat => {
                    const hour = stat.date_hour.split(' ')[1];
                    const hourIndex = parseInt(hour, 10);
                    data[hourIndex] = stat[key];
                });
                return data;
            } else if (period === 'week') {
                const data = Array(7).fill(0);
                const datesMap = stats.reduce((acc, stat) => {
                    acc[stat.date_day] = stat[key];
                    return acc;
                }, {});

                for (let i = 6; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    const dateString = date.toISOString().split('T')[0];
                    if (datesMap[dateString]) {
                        data[6 - i] = datesMap[dateString];
                    }
                }
                return data;
            } else if (period === 'month') {
                const daysInMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
                const data = Array(now.getDate()).fill(0);
                stats.forEach(stat => {
                    const day = parseInt(stat.date_day.split('-')[2], 10);
                    data[day - 1] = stat[key];
                });
                return data;
            } else if (period === 'year') {
                const data = Array(now.getMonth() + 1).fill(0);
                stats.forEach(stat => {
                    const month = parseInt(stat.date_month.split('-')[1], 10);
                    data[month - 1] = stat[key];
                });
                return data;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            createCharts();
        });
    </script>
</body>
</html>
