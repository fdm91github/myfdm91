<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Manutenzioni</title>
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

    <!-- Custom JS for toggling content visibility -->
    <script>
        $(document).ready(function() {
            $('.card-body').hide();  // Initially hide the card body content
            $('.toggle-content').click(function() {
                $(this).closest('.card').find('.card-body').toggle();  // Toggle visibility of card body
                $(this).find('i').toggleClass('bi-eye bi-eye-slash');  // Toggle eye icon
            });
        });
    </script>
</head>
<body>
    <div class="container mt-5">
        <h4>Manutenzioni</h4><br/>
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-0"><?php echo htmlspecialchars($vehicle['description']); ?> (<?php echo htmlspecialchars($vehicle['plateNumber']); ?>)</h4>
                    <div>
                        <button class="btn btn-primary add-service-btn" data-toggle="modal" data-target="#addServiceModal" data-vehicle-id="<?php echo $vehicle['id']; ?>">
                            <i class="bi bi-plus"></i>
                        </button>
                        <button class="btn btn-primary toggle-content">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
				<div class="card-body">
					<?php 
					$hasServices = false;
					if (isset($vehicleServices) && !empty($vehicleServices)): 
						foreach ($vehicleServices as $service):
							if ($service['vehicle_id'] == $vehicle['id']):
								if (!$hasServices): 
									$hasServices = true; 
					?>
					<div class="row"> <!-- Open this div only when there are services -->
					<?php endif; ?>
									<div class="col-12 mb-3">
										<div class="card">
											<div class="card-body d-flex justify-content-between align-items-center">
												<div>
													<div align=left>
														<h5 class="card-title mb-0"><?php echo htmlspecialchars($service['description']); ?></h5>
													</div><br/>
													<p class="mb-0">
														<strong>Costo:</strong> <?php echo htmlspecialchars($service['amount']); ?> <br/>
														<strong>Data di acquisto:</strong> <?php echo htmlspecialchars(formatDate($service['buying_date'])); ?> <br/>
														<strong>Eseguita a:</strong> <?php echo htmlspecialchars($service['registered_kilometers']); ?> km
													</p><br/>
													<button class="btn btn-info btn-sm" type="button" data-toggle="collapse" data-target="#details-<?php echo $service['id']; ?>" aria-expanded="false" aria-controls="details-<?php echo $service['id']; ?>">
														<i class="bi bi-info-circle"></i>
													</button>
													<?php if (!empty($service['attachment_path'])): ?>
														<a href="<?php echo $service['attachment_path']; ?>" class="btn btn-success btn-sm" download>
															<i class="bi bi-download"></i>
														</a>
													<?php endif; ?>
													<button class="btn btn-warning btn-sm" data-toggle="modal"
														data-target="#editServiceModal"
														data-id="<?php echo $service['id']; ?>"
														data-description="<?php echo htmlspecialchars($service['description']); ?>"
														data-amount="<?php echo htmlspecialchars($service['amount']); ?>"
														data-buying-date="<?php echo htmlspecialchars($service['buying_date']); ?>"
														data-registered-kilometers="<?php echo htmlspecialchars($service['registered_kilometers']); ?>">
														<i class="bi bi-pencil"></i>
													</button>
													<button class="btn btn-danger btn-sm" data-toggle="modal"
														data-target="#deleteServiceModal"
														data-id="<?php echo $service['id']; ?>">
														<i class="bi bi-trash"></i>
													</button>
												</div>
											</div>
											<div class="collapse mt-3" id="details-<?php echo $service['id']; ?>" style="background-color: #2c3e50; padding: 15px;">
												<?php if (isset($serviceParts[$service['id']]) && !empty($serviceParts[$service['id']])): ?>
													<ul class="list-group">
														<?php foreach ($serviceParts[$service['id']] as $part): ?>
															<li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: #34495e; color: #ecf0f1;">
																<?php echo htmlspecialchars($part['part_name']); ?>
																<span class="badge badge-secondary"><?php echo htmlspecialchars($part['part_number']); ?></span>
															</li>
														<?php endforeach; ?>
													</ul>
												<?php else: ?>
													<div class="alert alert-info" role="alert">
														Nessuna parte associata a questa manutenzione.
													</div>
												<?php endif; ?>
											</div>
										</div>
									</div>
								<?php 
									endif;
								endforeach; 
							endif; 
							?>

					<?php if ($hasServices): ?>
						</div> <!-- Close the row div only if it was opened -->
					<?php endif; ?>

					<?php if (!$hasServices): ?>
						<div class="alert" role="alert">
							Nessuna manutenzione registrata per questo veicolo.
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>

<!-- Footer placed outside of the card structure -->
<?php include '../footer.php'; ?>

<!-- Modals -->
<?php include 'addServiceModal.php'; ?>
<?php include 'editServiceModal.php'; ?>
<?php include 'deleteServiceModal.php'; ?>
<?php include 'navbar.php'; ?>

</body>
</html>
