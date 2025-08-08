<?php
session_start();
require_once '../config.php';

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>I miei Portafogli</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include '../script.php'; ?>
    <link href="../my.css" rel="stylesheet">
    <script>
        $(document).ready(function() {
            $('.card-body').hide();
            $('.toggle-content').click(function() {
                $(this).closest('.card').find('.card-body').toggle();
                $(this).find('i').toggleClass('bi-eye bi-eye-slash');
            });
        });
    </script>
</head>
<body>
	<?php
		include 'retrieveData.php';
	?>
	
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
								<label for="searchName">Descrizione:</label>
								<input type="text" id="searchName" class="form-control" placeholder="Cerca un elemento...">
							</div>
							<div class="col-md-6">
								<?php
								// Eseguo un merge dei portafogli condivisi e personali
								$allWallets = $wallets;
								if (isset($sharedWallets) && !empty($sharedWallets)) {
									$allWallets = array_merge($allWallets, $sharedWallets);
								}
								?>
								<label for="walletFilter">Portafogli:</label>
								<select id="walletFilter" class="form-control">
									<option value="all" selected>Tutti i Portafogli</option>
									<?php foreach ($allWallets as $wallet): ?>
										<?php if (!is_null($wallet['deleted_at'])) continue; ?>
										<option value="<?php echo htmlspecialchars($wallet['id']); ?>">
											<?php echo htmlspecialchars($wallet['description']); ?>
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
			
			<?php foreach ($wallets as $wallet): ?>
				<?php if (!is_null($wallet['deleted_at'])) continue; // Skip deleted wallets ?>
				<div class="card mb-4">
					<div class="card-header d-flex justify-content-between align-items-center flex-wrap">
						<h4 class="mb-0"><i class="bi bi-<?php echo htmlspecialchars($wallet['icon']); ?>"></i> <?php echo htmlspecialchars($wallet['description']); ?></h4>
						<div>
							<button class="btn btn-primary add-wallet-data-btn" data-bs-toggle="modal" data-bs-target="#addWalletDataModal" data-wallet-id="<?php echo $wallet['id']; ?>">
								<i class="bi bi-plus"></i>
							</button>
							<button class="btn btn-primary toggle-content">
								<i class="bi bi-eye"></i>
							</button>
						</div>
					</div>
					<div class="card-body">
						<?php
						// Create an array of wallet data that belongs to the current wallet.
						$currentWalletData = array();
						if (isset($walletDatas) && !empty($walletDatas)) {
							foreach ($walletDatas as $walletData) {
								if ($walletData['wallet_id'] == $wallet['id']) {
									$currentWalletData[] = $walletData;
								}
							}
						}
						?>

						<?php if (isset($sharedWalletSettlements[$wallet['id']])): ?>
							<?php $settlement = $sharedWalletSettlements[$wallet['id']]; ?>
							<div class="mt-3">
								<p><strong>Totale spese:</strong> 
									<?php echo number_format($settlement['totalExpense'], 2, ',', '.'); ?>€
								</p>
								<p><strong>Quota equa:</strong> 
									<?php echo number_format($settlement['equalShare'], 2, ',', '.'); ?>€
								</p>
								<ul class="list-group">
									<?php foreach ($settlement['participants'] as $pid => $info): ?>
										<li class="list-group-item">
											<strong><?php echo htmlspecialchars($info['username']); ?></strong>
											<?php
												if ($info['net'] > 0) {
													echo "deve ricevere " . number_format($info['net'], 2, ',', '.') . "€";
												} elseif ($info['net'] < 0) {
													echo "deve pagare " . number_format(abs($info['net']), 2, ',', '.') . "€";
												} else {
													echo "è in linea con la media delle spese";
												}
											?>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
						
						<br/>

						<?php if (!empty($currentWalletData)): ?>
							<div class="row">
								<?php foreach ($currentWalletData as $walletData): ?>
									<div class="col-12 mb-3">
										<div class="card">
											<div class="card-body d-flex justify-content-between align-items-center">
												<div>
													<div align="left">
														<h5 class="card-title mb-0"><?php echo htmlspecialchars($walletData['description']); ?></h5>
													</div><br/>
													<p class="mb-0">
														<strong>Costo:</strong> <?php echo htmlspecialchars($walletData['amount']); ?>€<br/>
														<strong>Data di acquisto:</strong> <?php echo htmlspecialchars(formatDate($walletData['buying_date'])); ?><br/>
													</p>
													<?php if($walletData['created_by'] != $_SESSION['id']): ?>
														<small>Spesa inserita da <?php echo htmlspecialchars($walletData['username']) ?></small>
													<?php endif; ?>
													<br/>
													<?php if (
														isset($walletDataPartsById[$walletData['id']])
														&& !empty($walletDataPartsById[$walletData['id']])
													): ?>
														<button class="btn btn-info btn-sm"
																type="button"
																data-bs-toggle="collapse"
																data-bs-target="#details-<?php echo $walletData['id']; ?>"
																aria-expanded="false"
																aria-controls="details-<?php echo $walletData['id']; ?>">
															<i class="bi bi-info-circle"></i>
														</button>
													<?php endif; ?>
													<?php if($walletData['created_by'] == $_SESSION['id']): ?>
														<button class="btn btn-warning btn-sm" data-bs-toggle="modal"
															data-bs-target="#editWalletDataModal"
															data-id="<?php echo $walletData['id']; ?>"
															data-description="<?php echo htmlspecialchars($walletData['description']); ?>"
															data-amount="<?php echo htmlspecialchars($walletData['amount']); ?>"
															data-buying-date="<?php echo htmlspecialchars($walletData['buying_date']); ?>">
															<i class="bi bi-pencil"></i>
														</button>
														<button class="btn btn-danger btn-sm" data-bs-toggle="modal"
															data-bs-target="#deleteWalletDataModal"
															data-id="<?php echo $walletData['id']; ?>">
															<i class="bi bi-trash"></i>
														</button>
													<?php endif; ?>
												</div>
											</div>
											<div class="collapse mt-3" id="details-<?php echo $walletData['id']; ?>" style="background-color: #2c3e50; padding: 15px;">
												<?php if (isset($walletDataPartsById[$walletData['id']]) && !empty($walletDataPartsById[$walletData['id']])): ?>
													<ul class="list-group">
														<?php foreach ($walletDataPartsById[$walletData['id']] as $part): ?>
															<li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: #34495e; color: #ecf0f1;">
																<?php echo htmlspecialchars($part['part_name']); ?>
																<span class="badge bg-secondary"><?php echo htmlspecialchars($part['part_cost']); ?>€</span>
															</li>
														<?php endforeach; ?>
													</ul>
												<?php else: ?>
													<div class="alert alert-info" role="alert">
														Nessun prodotto associato a questa spesa.
													</div>
												<?php endif; ?>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="alert" role="alert">
								Nessuna spesa registrata per questo Portafogli.
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
			
			<!-- Section for Shared Wallets -->
			<?php if(isset($sharedWallets) && !empty($sharedWallets)): ?>
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between align-items-center flex-wrap">
					<h4 class="mb-0"><i class="bi bi-share"></i> Portafogli condivisi con me</h4>
					<div>
						<button class="btn btn-primary toggle-content">
							<i class="bi bi-eye"></i>
						</button>
					</div>
				</div>
				<div class="card-body">
					<?php foreach ($sharedWallets as $wallet): ?>
						<div class="card mb-4">
							<div class="card-header d-flex justify-content-between align-items-center flex-wrap">
								<h4 class="mb-0"><i class="bi bi-<?php echo htmlspecialchars($wallet['icon']); ?>"></i> <?php echo htmlspecialchars($wallet['description']); ?></h4>
								<div>
									<button class="btn btn-primary add-wallet-data-btn" data-bs-toggle="modal" data-bs-target="#addWalletDataModal" data-wallet-id="<?php echo $wallet['id']; ?>">
										<i class="bi bi-plus"></i>
									</button>
									<button class="btn btn-primary toggle-content">
										<i class="bi bi-eye"></i>
									</button>
								</div>
							</div>
							<div class="card-body">
							
								<?php if (isset($sharedWalletSettlements[$wallet['id']])): ?>
									<?php $settlement = $sharedWalletSettlements[$wallet['id']]; ?>
									<div class="mt-3">
										<p><strong>Totale spese:</strong> 
											<?php echo number_format($settlement['totalExpense'], 2, ',', '.'); ?>€
										</p>
										<p><strong>Quota equa:</strong> 
											<?php echo number_format($settlement['equalShare'], 2, ',', '.'); ?>€
										</p>
										<ul class="list-group">
											<?php foreach ($settlement['participants'] as $pid => $info): ?>
												<li class="list-group-item">
													<strong><?php echo htmlspecialchars($info['username']); ?></strong>
													<?php
														if ($info['net'] > 0) {
															echo "deve ricevere " . number_format($info['net'], 2, ',', '.') . "€";
														} elseif ($info['net'] < 0) {
															echo "deve pagare " . number_format(abs($info['net']), 2, ',', '.') . "€";
														} else {
															echo "è in linea con la media delle spese";
														}
													?>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php endif; ?>
								
								<br/>
						
								<?php
								// Gather expenses for this shared wallet.
								$currentWalletData = array();
								if (isset($sharedWalletDatas) && !empty($sharedWalletDatas)) {
									foreach ($sharedWalletDatas as $walletData) {
										if ($walletData['wallet_id'] == $wallet['id']) {
											$currentWalletData[] = $walletData;
										}
									}
								}
								?>
								<?php if (!empty($currentWalletData)): ?>
									<div class="row">
										<?php foreach ($currentWalletData as $walletData): ?>
											<div class="col-12 mb-3">
												<div class="card">
													<div class="card-body d-flex justify-content-between align-items-center">
														<div>
															<div align="left">
																<h5 class="card-title mb-0"><?php echo htmlspecialchars($walletData['description']); ?></h5>
															</div><br/>
															<p class="mb-0">
																<strong>Costo:</strong> <?php echo htmlspecialchars($walletData['amount']); ?>€<br/>
																<strong>Data di acquisto:</strong> <?php echo htmlspecialchars(formatDate($walletData['buying_date'])); ?><br/>
															</p>
															<?php if($walletData['created_by'] != $_SESSION['id']): ?>
																<small>Spesa inserita da <?php echo htmlspecialchars($walletData['username']) ?></small>
															<?php endif; ?>
															<br/>
															<button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $walletData['id']; ?>" aria-expanded="false" aria-controls="details-<?php echo $walletData['id']; ?>">
																<i class="bi bi-info-circle"></i>
															</button>
															<?php if($walletData['created_by'] == $_SESSION['id']): ?>
																<!-- Only show edit/delete if the current user created this expense -->
																<button class="btn btn-warning btn-sm" data-bs-toggle="modal"
																	data-bs-target="#editWalletDataModal"
																	data-id="<?php echo $walletData['id']; ?>"
																	data-description="<?php echo htmlspecialchars($walletData['description']); ?>"
																	data-amount="<?php echo htmlspecialchars($walletData['amount']); ?>"
																	data-buying-date="<?php echo htmlspecialchars($walletData['buying_date']); ?>">
																	<i class="bi bi-pencil"></i>
																</button>
																<button class="btn btn-danger btn-sm" data-bs-toggle="modal"
																	data-bs-target="#deleteWalletDataModal"
																	data-id="<?php echo $walletData['id']; ?>">
																	<i class="bi bi-trash"></i>
																</button>
															<?php endif; ?>
														</div>
													</div>
													<div class="collapse mt-3" id="details-<?php echo $walletData['id']; ?>" style="background-color: #2c3e50; padding: 15px;">
														<?php if (isset($walletDataPartsById[$walletData['id']]) && !empty($walletDataPartsById[$walletData['id']])): ?>
															<ul class="list-group">
																<?php foreach ($walletDataPartsById[$walletData['id']] as $part): ?>
																	<li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: #34495e; color: #ecf0f1;">
																		<?php echo htmlspecialchars($part['part_name']); ?>
																		<span class="badge bg-secondary"><?php echo htmlspecialchars($part['part_cost']); ?>€</span>
																	</li>
																<?php endforeach; ?>
															</ul>
														<?php else: ?>
															<div class="alert alert-info" role="alert">
																Nessun prodotto associato a questa spesa.
															</div>
														<?php endif; ?>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								<?php else: ?>
									<div class="alert" role="alert">
										Nessuna spesa registrata per questo Portafoglio.
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Footer placed outside of the card structure -->
	<?php include '../footer.php'; ?>

	<!-- Modals -->
	<?php include 'addWalletDataModal.php'; ?>
	<?php include 'editWalletDataModal.php'; ?>
	<?php include 'deleteWalletDataModal.php'; ?>
	<?php include 'navbar.php'; ?>

</body>
</html>

<script>
$(document).ready(function () {
    function filterWalletDatas() {
        let searchName = $("#searchName").val().toLowerCase();
        let selectedWallet = $("#walletFilter").val();
        let minCost = parseFloat($("#minCost").val()) || 0;
        let maxCost = parseFloat($("#maxCost").val()) || Infinity;

        $(".card.mb-4").each(function () {
            let isFilterCard = $(this).find("h4").text().includes("Filtri");
            let walletId = $(this).find(".add-wallet-data-btn").data("wallet-id");
            let matchesWallet = selectedWallet === "all" || selectedWallet == walletId;

            if (!isFilterCard) {
                let showWallet = false;

                $(this).find(".card-body .card").each(function () {
                    let walletDataName = $(this).find(".card-title").text().toLowerCase();
                    let costText = $(this).find("p:contains('Costo:')").text().replace('Costo:', '').replace('€', '').trim();
                    let cost = parseFloat(costText) || 0;

                    let matchesSearch = walletDataName.includes(searchName);
                    let matchesCost = cost >= minCost && cost <= maxCost;

                    let showWalletData = matchesSearch && matchesCost;
                    $(this).toggle(showWalletData);

                    if (showWalletData) showWallet = true;
                });

                $(this).toggle(matchesWallet && showWallet);
            }
        });
    }

    $("#searchName, #walletFilter, #minCost, #maxCost").on("input change", function () {
        filterWalletDatas();

        let collapseElement = document.getElementById("filterContent");
        let bsCollapse = new bootstrap.Collapse(collapseElement, {toggle: false});
        bsCollapse.show();
    });
});
</script>
