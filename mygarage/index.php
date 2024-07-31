<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['month']) && isset($_POST['year'])) {
    $selectedMonth = $_POST['month'];
    $selectedYear = $_POST['year'];
} else {
    $selectedMonth = date('m');
    $selectedYear = date('Y');
}

if ($salaryDate) {
    $currentDate = DateTime::createFromFormat('Y-m-d', "$selectedYear-$selectedMonth-$salaryDate");
    $next_date = clone $currentDate;
    $next_date->modify('+1 month')->modify('-1 day');
} else {
    $currentDate = DateTime::createFromFormat('Y-m-d', "$selectedYear-$selectedMonth-01");
    $next_date = clone $currentDate;
    $next_date->modify('+1 month')->modify('-1 day');
}

$currentSelected = $currentDate->format('d/m/Y');
$next_selected = $next_date->format('d/m/Y');
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS e dipendenze varie -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <!-- Charts e grafici -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
	<link href="../my.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
	<h2>MyGarage</h2>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="mb-0">Panoramica</h4>
                <form method="POST" action="" class="form-inline">
                    <label for="month">Mese:</label>
                    <select name="month" id="month" class="form-control mx-2">
                        <?php
                        $months = [
                            '01' => 'Gennaio', '02' => 'Febbraio', '03' => 'Marzo',
                            '04' => 'Aprile', '05' => 'Maggio', '06' => 'Giugno',
                            '07' => 'Luglio', '08' => 'Agosto', '09' => 'Settembre',
                            '10' => 'Ottobre', '11' => 'Novembre', '12' => 'Dicembre'
                        ];

                        for ($m = 1; $m <= 12; $m++) {
                            $month = str_pad($m, 2, '0', STR_PAD_LEFT);
                            $month_name = $months[$month];
                            echo "<option value='$month'" . ($selectedMonth == $month ? ' selected' : '') . ">$month_name</option>";
                        }
                        ?>
                    </select>
                    <label for="year">Anno:</label>
                    <select name="year" id="year" class="form-control mx-2">
                        <?php
                        for ($y = 2020; $y <= 2099; $y++) {
                            echo "<option value='$y'" . ($selectedYear == $y ? ' selected' : '') . ">$y</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-primary ml-2">Visualizza</button>
                </form>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <p><b>Entrate totali</b> per il periodo <?php echo $currentSelected . ' - ' . $next_selected; ?>: <b><?php echo isset($thisMonthIncomes) ? $thisMonthIncomes : '0'; ?> euro</b></p>
                        <p><b>Totale spese ricorrenti</b> per il periodo <?php echo $currentSelected . ' - ' . $next_selected; ?>: <b><?php echo $thisMonthTotalRecurringExpenses; ?> euro</b></p>
                        <p><b>Totale spese stimate</b> per il periodo <?php echo $currentSelected . ' - ' . $next_selected; ?>: <b><?php echo $thisMonthTotalEstimatedExpenses; ?> euro</b></p>
                        <p><b>Totale spese extra</b> per il periodo <?php echo $currentSelected . ' - ' . $next_selected; ?>: <b><?php echo $thisMonthTotalExtraExpenses; ?> euro</b></p>
                        <p><b>Spese totali</b> per il periodo <?php echo $currentSelected . ' - ' . $next_selected; ?>: <b><?php echo $totalExpenses; ?> euro</b></p>
						<p><b>Stipendio rimanente</b> per il periodo <?php echo $currentSelected . ' - ' . $next_selected; ?>: <b><?php echo $leftIncomes; ?> euro</b></p>
                    </div>
                    <div class="col-12 col-md-6">
						<p><b>Totale nel salvadanaio:</b> <b><?php if($totalPiggyBank > 0) {echo $totalPiggyBank;} else {echo '0';} ?> euro</b> di cui <b><?php if($thisMonthPiggyBank > 0) {echo $thisMonthPiggyBank;} else {echo '0';} ?> euro</b> aggiunti nel periodo <?php echo $currentSelected . ' - ' . $next_selected; ?></p>
                        <p>Sul salvadanaio delle Spese Ricorrenti dovresti avere <b><?php echo $recurringSavings; ?> euro</b></p>
                        <p>Sul salvadanaio delle Spese Stimate dovresti avere <b><?php echo $estimatedSavings; ?> euro</b></p>
                    </div>
                </div>
            </div>
        </div>

		<div class="row">
			<div class="col-12 col-md-6 mb-4">
				<div class="card">
					<div class="card-header">
						<h4 class="mb-0">Distribuzione Spese</h4>
					</div>
					<div class="card-body">
						<canvas id="expenseChart" width="300" height="300"></canvas>
					</div>
				</div>
			</div>

			<div class="col-12 col-md-6 mb-4">
				<div class="card">
					<div class="card-header">
						<h4 class="mb-0">Entrate e Uscite degli Ultimi 12 Mesi</h4>
					</div>
					<div class="card-body">
						<canvas id="incomeExpenseChart" width="300" height="300"></canvas>                    
					</div>
				</div>
			</div>
		</div>

		<script>
			document.addEventListener("DOMContentLoaded", function() {
				var ctx = document.getElementById('expenseChart').getContext('2d');
				var expenseChart = new Chart(ctx, {
					type: 'pie',
					data: {
						labels: ['Tot. spese ricorrenti', 'Tot. spese stimate', 'Tot. spese extra', 'Salvadanaio', 'Stipendio rimanente'],
						datasets: [{
							data: [
								<?php echo $thisMonthTotalRecurringExpenses; ?>,
								<?php echo $thisMonthTotalEstimatedExpenses; ?>,
								<?php echo $thisMonthTotalExtraExpenses; ?>,
								<?php echo $thisMonthPiggyBank; ?>,
								<?php echo $leftIncomes; ?>
							],
							backgroundColor: [
								'rgba(255, 99, 132, 1)',
								'rgba(54, 162, 235, 1)',
								'rgba(255, 206, 86, 1)',
								'rgba(75, 192, 192, 1)',
								'rgba(153, 102, 255, 1)'
							],
							borderColor: [
								'rgba(255, 99, 132, 1)',
								'rgba(54, 162, 235, 1)',
								'rgba(255, 206, 86, 1)',
								'rgba(75, 192, 192, 1)',
								'rgba(153, 102, 255, 1)'
							],
							borderWidth: 1
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						plugins: {
							legend: {
								labels: {
									color: '#ecf0f1' // Colore del testo della legenda
								}
							}
						}
					}
				});

				var incomeExpenseData = {
					labels: [<?php echo implode(", ", array_map(function($month) { return "'$month'"; }, $last_12_months)); ?>],
					datasets: [{
						label: 'Entrate',
						data: [<?php echo implode(", ", $last_12_monthlyIncomes); ?>],
						backgroundColor: 'rgba(75, 192, 192, 1)',
						borderColor: 'rgba(75, 192, 192, 1)',
						borderWidth: 1
					},
					{
						label: 'Uscite',
						data: [<?php echo implode(", ", $last_12_monthlyExpenses); ?>],
						backgroundColor: 'rgba(255, 99, 132, 1)',
						borderColor: 'rgba(255, 99, 132, 1)',
						borderWidth: 1
					},
					{
						label: 'Salvadanaio',
						data: [<?php echo implode(", ", $monthlyPiggyBank); ?>],
						backgroundColor: 'rgba(153, 102, 255, 1)',
						borderColor: 'rgba(153, 102, 255, 1)',
						borderWidth: 1
					}]
				};

				var ctx2 = document.getElementById('incomeExpenseChart').getContext('2d');
				var incomeExpenseChart = new Chart(ctx2, {
					type: 'bar',
					data: incomeExpenseData,
					options: {
						responsive: true,
						maintainAspectRatio: false,
						scales: {
							y: {
								beginAtZero: true,
								ticks: {
									color: '#ecf0f1' // Colore del testo dell'asse Y
								}
							},
							x: {
								ticks: {
									color: '#ecf0f1' // Colore del testo dell'asse X
								}
							}
						},
						plugins: {
							legend: {
								labels: {
									color: '#ecf0f1' // Colore del testo della legenda
								}
							}
						}
					}
				});
			});
		</script>
    </div>
</body>
</html>
