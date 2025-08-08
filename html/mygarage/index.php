<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Centralized updated scripts (Bootstrap 5.3.3, jQuery, Popper, Chart.js, etc.) -->
    <?php include '../script.php'; ?>
    <!-- Bootstrap Icons (if not already included in /script.php) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../my.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
	<div class="content-wrapper">
		<div class="container mt-5">
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between align-items-center flex-wrap">
					<h4 class="mb-0">Panoramica</h4>
				</div>

				<div class="card-body">
					<div class="row">
						<div class="col-12 col-md-6">
							<h5>Riepilogo</h5><br/>
							<p><b>Nell'ultimo anno hai speso <?php echo isset($lastYearExpenses) ? $lastYearExpenses : '0'; ?> euro</b></p>
							<p><b>Negli ultimi 12 mesi hai effettuato <?php echo isset($lastYearMaintenances) ? $lastYearMaintenances : '0'; ?> manutenzioni</b></p>
							<p><b>L'ultima manutenzione è costata <?php echo isset($lastMaintenanceCost) ? $lastMaintenanceCost : '0'; ?> euro</b></p>
						</div>
						<div class="col-12 col-md-6">
							<h5>Prossime scadenze</h5><br/>
							<p><b><?php echo isset($nearestTaxExpirationVehicle) ? "Paga il bollo del veicolo $nearestTaxExpirationVehicle entro il " . htmlspecialchars(formatDate($nearestTaxExpirationDate)) : "Nessuna scadenza impostata per il bollo"; ?></b></p>
							<p><b><?php echo isset($nearestRevisionExpirationDate) ? "Paga la revisione del veicolo $nearestRevisionExpirationVehicle entro il " . htmlspecialchars(formatDate($nearestRevisionExpirationDate)) : "Nessuna scadenza impostata per la revisione"; ?></b></p>
							<p><b><?php echo isset($nearestInsuranceExpirationDate) ? "Rinnova l'assicurazione del veicolo $nearestInsuranceExpirationVehicle entro il " . htmlspecialchars(formatDate($nearestInsuranceExpirationDate)) : "Nessuna scadenza impostata per l'assicurazione"; ?></b></p>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-12 col-md-6 mb-4">
					<div class="card">
						<div class="card-header">
							<h4 class="mb-0">Spese totali del <?php echo date('Y'); ?></h4>
						</div>
						<div class="card-body">
							<canvas id="YearlyExpenseChart" width="300" height="300"></canvas>
						</div>
					</div>
				</div>

				<div class="col-12 col-md-6 mb-4">
					<div class="card">
						<div class="card-header">
							<h4 class="mb-0">Statistiche degli ultimi 12 mesi</h4>
						</div>
						<div class="card-body">
							<canvas id="YearExpenseChart" width="300" height="300"></canvas>                    
						</div>
					</div>
				</div>
			</div>

			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between align-items-center flex-wrap">
					<h4 class="mb-0">KM percorsi</h4>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-12">
							<canvas id="travelledkmChart" width="1200" height="250"></canvas>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
    <?php include '../footer.php'; ?>
</body>
</html>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById('YearlyExpenseChart').getContext('2d');
        var YearlyExpenseChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Assicurazioni', 'Bolli', 'Manutenzioni', 'Revisioni'],
                datasets: [{
                    data: [
                        <?php echo $thisYearInsuranceExpenses; ?>,
                        <?php echo $thisYearTaxExpenses; ?>,
                        <?php echo $thisYearMaintenanceExpenses; ?>,
                        <?php echo $thisYearRevisionExpenses; ?>
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
                            color: '#ecf0f1'
                        }
                    }
                }
            }
        });

        var YearlyStats = {
            labels: [<?php echo implode(", ", array_map(function($month) { return "'$month'"; }, $last_12_months)); ?>],
            datasets: [{
                label: 'Assicurazioni',
                data: [<?php echo implode(", ", $last_12_insuranceExpenses); ?>],
                backgroundColor: 'rgba(255, 99, 132, 1)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            },
            {
                label: 'Bolli',
                data: [<?php echo implode(", ", $last_12_taxExpenses); ?>],
                backgroundColor: 'rgba(54, 162, 235, 1)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            },
            {
                label: 'Manutenzioni',
                data: [<?php echo implode(", ", $last_12_maintenanceExpenses); ?>],
                backgroundColor: 'rgba(255, 206, 86, 1)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1
            },
            {
                label: 'Revisioni',
                data: [<?php echo implode(", ", $last_12_revisionExpenses); ?>],
                backgroundColor: 'rgba(75, 192, 192, 1)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };

        var ctx2 = document.getElementById('YearExpenseChart').getContext('2d');
        var YearExpenseChart = new Chart(ctx2, {
            type: 'bar',
            data: YearlyStats,
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
        
        var ctx3 = document.getElementById('travelledkmChart').getContext('2d');
        var datasets = [];

        <?php foreach ($last_12_travelledKms as $vehicleData): ?>
            datasets.push({
                label: '<?php echo $vehicleData['vehicle']; ?>',
                data: [<?php echo implode(", ", $vehicleData['kms']); ?>],
                backgroundColor: 'rgba(0, 0, 0, 0)',
                borderColor: '<?php echo $vehicleData['color']; ?>',
                borderWidth: 2,
                fill: false
            });
        <?php endforeach; ?>

        var travelledKmChart = new Chart(ctx3, {
            type: 'line',
            data: {
                labels: [<?php echo implode(", ", array_map(function($month) { return "'$month'"; }, $last_12_months)); ?>],
                datasets: datasets
            },
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