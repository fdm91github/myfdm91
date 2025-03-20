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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include '../script.php'; ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../my.css" rel="stylesheet">

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
	<div class="content-wrapper">
		<div class="container mt-5">
			
			<!-- Filter Card -->
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h4 class="mb-0">Filtri</h4>
					<button class="btn btn-primary toggle-content" data-bs-toggle="collapse" data-bs-target="#filterContent" aria-expanded="false" aria-controls="filterContent">
						<i class="bi bi-funnel"></i>
					</button>
				</div>
				<div id="filterContent" class="collapse card-body">
					<div class="mb-3">
						<div class="row mb-2">
							<div class="col-md-6">
								<label for="searchName">Nome Manutenzione:</label>
								<input type="text" id="searchName" class="form-control" placeholder="Cerca manutenzione...">
							</div>
							<div class="col-md-6">
								<label for="vehicleFilter">Veicolo:</label>
								<select id="vehicleFilter" class="form-control">
									<option value="all" selected>Tutti i veicoli</option>
									<?php foreach ($vehicles as $vehicle): ?>
										<option value="<?php echo htmlspecialchars($vehicle['id']); ?>">
											<?php echo htmlspecialchars($vehicle['description']) . " (" . htmlspecialchars($vehicle['plateNumber']) . ")"; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="row mb-2">
							<div class="col-md-6">
								<label for="minCost">Costo Minimo (€):</label>
								<input type="number" id="minCost" class="form-control" min="0" step="0.01">
							</div>
							<div class="col-md-6">
								<label for="maxCost">Costo Massimo (€):</label>
								<input type="number" id="maxCost" class="form-control" min="0" step="0.01">
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<?php foreach ($vehicles as $vehicle): ?>
				<div class="card mb-4">
					<div class="card-header d-flex justify-content-between align-items-center flex-wrap">
						<h4 class="mb-0"><?php echo htmlspecialchars($vehicle['description']); ?> (<?php echo htmlspecialchars($vehicle['plateNumber']); ?>)</h4>
						<div>
							<button class="btn btn-primary add-service-btn" data-bs-toggle="modal" data-bs-target="#addServiceModal" data-vehicle-id="<?php echo $vehicle['id']; ?>">
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
										<div class="row">
									<?php endif; ?>
									<div class="col-12 mb-3">
										<div class="card">
											<div class="card-body d-flex justify-content-between align-items-center">
												<div>
													<div align="left">
														<h5 class="card-title mb-0"><?php echo htmlspecialchars($service['description']); ?></h5>
													</div><br/>
													<p class="mb-0">
														<strong>Costo:</strong> <?php echo htmlspecialchars($service['amount']); ?> <br/>
														<strong>Data di acquisto:</strong> <?php echo htmlspecialchars(formatDate($service['buying_date'])); ?> <br/>
														<strong>Eseguita a:</strong> <?php echo htmlspecialchars($service['registered_kilometers']); ?> km
													</p><br/>
													<button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $service['id']; ?>" aria-expanded="false" aria-controls="details-<?php echo $service['id']; ?>">
														<i class="bi bi-info-circle"></i>
													</button>
													<?php if (!empty($service['attachment_path'])): ?>
														<a href="<?php echo $service['attachment_path']; ?>" class="btn btn-success btn-sm" download>
															<i class="bi bi-download"></i>
														</a>
													<?php endif; ?>
													<button class="btn btn-warning btn-sm" data-bs-toggle="modal"
														data-bs-target="#editServiceModal"
														data-id="<?php echo $service['id']; ?>"
														data-description="<?php echo htmlspecialchars($service['description']); ?>"
														data-amount="<?php echo htmlspecialchars($service['amount']); ?>"
														data-buying-date="<?php echo htmlspecialchars($service['buying_date']); ?>"
														data-registered-kilometers="<?php echo htmlspecialchars($service['registered_kilometers']); ?>">
														<i class="bi bi-pencil"></i>
													</button>
													<button class="btn btn-danger btn-sm" data-bs-toggle="modal"
														data-bs-target="#deleteServiceModal"
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
																<span class="badge bg-secondary"><?php echo htmlspecialchars($part['part_number']); ?></span>
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
								<?php endif;
							endforeach;
						endif; ?>
						<?php if ($hasServices): ?>
							</div>
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

<script>
$(document).ready(function () {
    function filterServices() {
        let searchName = $("#searchName").val().toLowerCase();
        let selectedVehicle = $("#vehicleFilter").val();
        let minCost = parseFloat($("#minCost").val()) || 0;
        let maxCost = parseFloat($("#maxCost").val()) || Infinity;

        $(".card.mb-4").each(function () {
            let isFilterCard = $(this).find("h4").text().includes("Filtri");
            let vehicleId = $(this).find(".add-service-btn").data("vehicle-id");
            let matchesVehicle = selectedVehicle === "all" || selectedVehicle == vehicleId;

            if (!isFilterCard) {
                let showVehicle = false;

                $(this).find(".card-body .card").each(function () {
                    let serviceName = $(this).find(".card-title").text().toLowerCase();
                    let costText = $(this).find("p:contains('Costo:')").text().replace('Costo:', '').replace('€', '').trim();
                    let cost = parseFloat(costText) || 0;

                    let matchesSearch = serviceName.includes(searchName);
                    let matchesCost = cost >= minCost && cost <= maxCost;

                    let showService = matchesSearch && matchesCost;
                    $(this).toggle(showService);

                    if (showService) showVehicle = true;
                });

                $(this).toggle(matchesVehicle && showVehicle);
            }
        });
    }

    $("#searchName, #vehicleFilter, #minCost, #maxCost").on("input change", function () {
        filterServices();
        // Using Bootstrap 5 Collapse instance instead of the jQuery method from Bootstrap 4
        let collapseElement = document.getElementById("filterContent");
        let bsCollapse = new bootstrap.Collapse(collapseElement, {toggle: false});
        bsCollapse.show();
    });
});
</script>
