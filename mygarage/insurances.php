<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Assicurazioni</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Centralized updated scripts (Bootstrap 5.3.3, jQuery, Popper, Chart.js, etc.) -->
    <?php include '../script.php'; ?>
    <!-- Bootstrap Icons (if not already included in /script.php) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../my.css" rel="stylesheet">
</head>
<body>
	<div class="content-wrapper">
		<div class="container mt-5">
			<h4>Assicurazioni</h4><br/>
			<?php foreach ($vehicles as $vehicle): ?>
				<div class="card mb-4">
					<div class="card-header d-flex justify-content-between align-items-center flex-wrap">
						<h4 class="mb-0">
							<?php echo htmlspecialchars($vehicle['description']); ?> (<?php echo htmlspecialchars($vehicle['plateNumber']); ?>)
						</h4>
						<button class="btn btn-primary add-insurance-btn" 
							data-bs-toggle="modal" data-bs-target="#addInsuranceModal" 
							data-vehicle-id="<?php echo $vehicle['id']; ?>">
							<i class="bi bi-plus"></i>
						</button>
					</div>
					<div class="card-body">
						<?php 
						$hasInsurances = false;
						if (isset($vehicleInsurances) && !empty($vehicleInsurances)): 
							foreach ($vehicleInsurances as $insurance):
								if ($insurance['vehicle_id'] == $vehicle['id']):
									$hasInsurances = true;
						?>
									<div class="card mb-3">
										<div class="card-body">
											<div class="row align-items-center">
												<div class="col">
													<h5 class="card-title"><?php echo htmlspecialchars($insurance['company']); ?></h5>
													<p class="card-text">
														<strong>Costo:</strong> <?php echo htmlspecialchars($insurance['amount']); ?><br>
														<strong>Data di acquisto:</strong> <?php echo htmlspecialchars(formatDate($insurance['buying_date'])); ?><br>
														<strong>Decorrenza:</strong> <?php echo htmlspecialchars(formatDate($insurance['effective_date'])); ?>
													</p>
												</div>
												<div class="col-auto">
													<button class="btn btn-warning btn-sm me-2"
														data-bs-toggle="modal"
														data-bs-target="#editInsuranceModal"
														data-id="<?php echo $insurance['id']; ?>"
														data-company="<?php echo htmlspecialchars($insurance['company']); ?>"
														data-amount="<?php echo htmlspecialchars($insurance['amount']); ?>"
														data-buying-date="<?php echo htmlspecialchars($insurance['buying_date']); ?>"
														data-effective-date="<?php echo htmlspecialchars($insurance['effective_date']); ?>">
														<i class="bi bi-pencil"></i>
													</button>
													<button class="btn btn-danger btn-sm"
														data-bs-toggle="modal"
														data-bs-target="#deleteInsuranceModal"
														data-id="<?php echo $insurance['id']; ?>">
														<i class="bi bi-trash"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
						<?php 
								endif;
							endforeach; 
						endif; 
						?>
						<?php if (!$hasInsurances): ?>
							<div class="alert" role="alert">
								Nessuna assicurazione registrata per questo veicolo
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
    <?php include 'addInsuranceModal.php'; ?>
    <?php include 'editInsuranceModal.php'; ?>
    <?php include 'deleteInsuranceModal.php'; ?>
    <?php include 'navbar.php'; ?>
    <?php include '../footer.php'; ?>
</body>
</html>
