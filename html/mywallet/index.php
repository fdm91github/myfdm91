<?php
session_start();
require_once '../config.php';

$months = [
	'01' => 'Gennaio', '02' => 'Febbraio', '03' => 'Marzo',
	'04' => 'Aprile', '05' => 'Maggio', '06' => 'Giugno',
	'07' => 'Luglio', '08' => 'Agosto', '09' => 'Settembre',
	'10' => 'Ottobre', '11' => 'Novembre', '12' => 'Dicembre'
];

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include '../script.php'; ?>
    <link href="../my.css" rel="stylesheet">
</head>
<body>
	<?php
		include 'retrieveData.php';
	?>
	<div class="content-wrapper">
		<div class="container mt-5">
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between align-items-center flex-wrap">
					<h4 class="mb-0">Panoramica <?php echo $months[$selectedMonth] . ' ' . $selectedYear; ?></h4>
					<form method="POST" action="" class="d-flex align-items-center">
						<label for="month" class="me-2">Mese:</label>
						<select name="month" id="month" class="form-control mx-2">
							<?php
							for ($m = 1; $m <= 12; $m++) {
								$month = str_pad($m, 2, '0', STR_PAD_LEFT);
								$month_name = $months[$month];
								echo "<option value='$month'" . ($selectedMonth == $month ? ' selected' : '') . ">$month_name</option>";
							}
							?>
						</select>
						<label for="year" class="me-2">Anno:</label>
						<select name="year" id="year" class="form-control mx-2">
							<?php
							for ($y = 2020; $y <= 2099; $y++) {
								echo "<option value='$y'" . ($selectedYear == $y ? ' selected' : '') . ">$y</option>";
							}
							?>
						</select>
						<button type="submit" class="btn btn-primary ms-2">Visualizza</button>
					</form>
				</div>

				<div class="card-body">
					<div class="row">
						<div class="col-12 col-md-6">
							<p><b>Entrate totali: <?php echo isset($thisMonthIncomes) ? $thisMonthIncomes : '0'; ?>€</b></p>
							<p><b>Totale spese ricorrenti: <?php echo $thisMonthTotalRecurringExpenses; ?>€</b></p>
							<p><b>Totale spese stimate: <?php echo $thisMonthTotalEstimatedExpenses; ?>€</b></p>
							<?php if (!empty($walletDashboardExpenses)): ?>
								<?php foreach ($walletDashboardExpenses as $wallet_name => $total): ?>
									<p><b>Totale <?php echo $wallet_name; ?>: <?php echo $total; ?>€</b></p>
								<?php endforeach; ?>
							<?php endif; ?>
							<p><b>Spese totali: <?php echo $totalExpenses; ?>€</b></p>
						</div>
						<div class="col-12 col-md-6">
							<p><b>Totale nel salvadanaio: <?php echo ($totalPiggyBank > 0 ? $totalPiggyBank : '0'); ?>€</b></p>
							<p>Aggiunti nel <b>Salvadanaio: <?php echo ($thisMonthPiggyBank > 0 ? $thisMonthPiggyBank : '0'); ?>€</b></p>
							<p>Tot. su Salvadanaio <b>Spese Ricorrenti: <?php echo $recurringSavings; ?>€</b></p>
							<p>Tot. su Salvadanaio <b>Spese Stimate: <?php echo $estimatedSavings; ?>€</b></p>
							<p><b>Stipendio rimanente: <?php echo $leftIncomes; ?>€</b></p>
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
							<h4 class="mb-0">Andamento ultimi 12 mesi</h4>
						</div>
						<div class="card-body">
							<canvas id="incomeExpenseChart" width="300" height="300"></canvas>                    
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php include 'navbar.php'; ?>
    <?php include '../footer.php'; ?>
</body>
</html>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var expenseChartLabels = [
            'Tot. spese ricorrenti',
            'Tot. spese stimate',
            'Salvadanaio',
            'Stipendio rimanente'
        ];
        var expenseChartData = [
            <?php echo $thisMonthTotalRecurringExpenses; ?>,
            <?php echo $thisMonthTotalEstimatedExpenses; ?>,
            <?php echo $thisMonthPiggyBank; ?>,
            <?php echo $leftIncomes; ?>
        ];
        var expenseColors = [
            'rgba(255, 132, 18, 1)',
            'rgba(247, 60, 60, 1)',
            'rgba(69, 122, 255, 1)',
            'rgba(68, 179, 54, 1)'
        ];

        // Wallet slices da Portafogli custom
        var walletLabels = <?php echo json_encode($walletLabels); ?>;
        var walletValues = <?php echo json_encode($walletValues); ?>;
        var walletColors = [
            'rgba(0, 123, 255, 1)',
            'rgba(255, 193, 7, 1)',
            'rgba(40, 167, 69, 1)',
            'rgba(220, 53, 69, 1)',
            'rgba(23, 162, 184, 1)'
        ];
        walletColors = walletColors.slice(0, walletLabels.length);

        var allLabels = expenseChartLabels.concat(walletLabels);
        var allData = expenseChartData.concat(walletValues);
        var allColors = expenseColors.concat(walletColors);

        var ctx = document.getElementById('expenseChart').getContext('2d');
        var expenseChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: allLabels,
                datasets: [{
                    data: allData,
                    backgroundColor: allColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#ecf0f1'
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
                backgroundColor: 'rgba(68, 179, 54, 1)',
                borderWidth: 1
            },
            {
                label: 'Uscite',
                data: [<?php echo implode(", ", $last_12_monthlyExpenses); ?>],
                backgroundColor: 'rgba(247, 60, 60, 1)',
                borderWidth: 1
            },
            {
                label: 'Salvadanaio',
                data: [<?php echo implode(", ", $monthlyPiggyBank); ?>],
                backgroundColor: 'rgba(69, 122, 255, 1)',
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
                            color: '#ecf0f1'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#ecf0f1'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#ecf0f1'
                        }
                    }
                }
            }
        });
    });
</script>
