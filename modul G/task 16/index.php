<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Календарь</title>
    <style>
        body {
            font-family: sans-serif;
        }
        .calendar {
            width: 70%;
            margin: 20px auto;
            border: 1px solid #ccc;
        }
        .calendar-header {
            background-color: #f0f0f0;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .calendar-body {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }
        .calendar-day {
            padding: 10px;
            text-align: center;
            border: 1px solid #eee;
        }
        .today {
            background-color: #ddd;
        }
        .other-month {
            color: #999;
        }
        .arrow {
            cursor: pointer;
            font-size: 20px;
        }
    </style>
</head>
<body>

<?php

// Получаем текущий месяц и год
if (isset($_GET['month']) && isset($_GET['year'])) {
    $month = $_GET['month'];
    $year = $_GET['year'];
} else {
    $month = date('n'); // Числовое представление месяца, без ведущих нулей
    $year = date('Y');
}

// Получаем количество дней в месяце
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Получаем день недели для первого дня месяца (0 - воскресенье, 6 - суббота)
$firstDayOfMonth = date('w', strtotime($year . '-' . $month . '-01'));

// Получаем текущий день
$today = date('j'); // День месяца, без ведущих нулей
$currentMonth = date('n');
$currentYear = date('Y');

// Получаем количество дней в предыдущем месяце
$prevMonth = ($month == 1) ? 12 : $month - 1;
$prevYear = ($month == 1) ? $year - 1 : $year;
$daysInPrevMonth = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);

// Вычисляем дату начала для заполнения предыдущими днями
$startDayPrevMonth = $daysInPrevMonth - $firstDayOfMonth + 1;

// Получаем количество дней в следующем месяце
$nextMonth = ($month == 12) ? 1 : $month + 1;
$nextYear = ($month == 12) ? $year + 1 : $year;

$dayCounter = 1; // Счетчик для дней следующего месяца

?>

<div class="calendar">
    <div class="calendar-header">
        <a href="index.php?month=<?php echo ($month == 1 ? 12 : $month - 1); ?>&year=<?php echo ($month == 1 ? $year - 1 : $year); ?>" class="arrow">&lt;</a>
        <div style='display: flex; flex-direction:column;'>
            <span><?php echo date('F', strtotime($year . '-' . $month . '-01')); ?></span>
            <span><?php echo $year;?></span>
        </div>
        <a href="index.php?month=<?php echo ($month == 12 ? 1 : $month + 1); ?>&year=<?php echo ($month == 12 ? $year + 1 : $year); ?>" class="arrow">&gt;</a>
    </div>
    <div class="calendar-body">
        <div>Вс</div>
        <div>Пн</div>
        <div>Вт</div>
        <div>Ср</div>
        <div>Чт</div>
        <div>Пт</div>
        <div>Сб</div>

        <?php
        // Заполняем дни предыдущего месяца
        for ($i = 0; $i < $firstDayOfMonth; $i++) {
            echo '<div class="calendar-day other-month">' . $startDayPrevMonth . '</div>';
            $startDayPrevMonth++;
        }

        // Заполняем дни текущего месяца
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $class = 'calendar-day';
            if ($day == $today && $month == $currentMonth && $year == $currentYear) {
                $class .= ' today';
            }
            echo '<div class="' . $class . '">' . $day . '</div>';
        }

        // Заполняем дни следующего месяца
        $remainingDays = 7 - (($firstDayOfMonth + $daysInMonth) % 7);
        if ($remainingDays != 7) {
            for ($i = 0; $i < $remainingDays; $i++) {
                echo '<div class="calendar-day other-month">' . $dayCounter . '</div>';
                $dayCounter++;
            }
        }
        ?>
    </div>
</div>

</body>
</html>
