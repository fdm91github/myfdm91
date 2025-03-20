<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';

// Split owned wallets into active and inactive
$activeWallets = [];
$inactiveWallets = [];
foreach ($wallets as $wallet) {
    if (!$wallet['deleted_at']) {
        $activeWallets[] = $wallet;
    } else {
        $inactiveWallets[] = $wallet;
    }
}

// Helper function to display sharing info
function displaySharedInfo($shared_json) {
    $shared = json_decode($shared_json, true);
    if (!is_array($shared) || count($shared) === 0) {
        return "Non condiviso";
    } else {
        $names = array();
        foreach ($shared as $entry) {
            if (isset($entry['username'])) {
                $names[] = htmlspecialchars($entry['username']);
            }
        }
        return "Condiviso con " . implode(", ", $names);
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>I miei Portafogli</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include '../script.php'; ?>
    <link href="../my.css" rel="stylesheet">
</head>
<body>
	<div class="content-wrapper">
		<div class="container mt-5">
            <!-- Section for Owned Wallets -->
			<div class="mb-4">
				<div class="d-flex justify-content-between align-items-center flex-wrap">
					<h4 class="mb-0">I miei Portafogli</h4>
					<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWalletModal">
						<i class="bi bi-plus"></i>
					</button>
				</div>
				<div class="row mt-3">
					<?php foreach ($activeWallets as $wallet): ?>
						<div class="col-md-4">
							<div class="card mb-3 position-relative">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-center">
										<h5 class="card-title mb-0">
											<i class="bi bi-<?php echo htmlspecialchars($wallet['icon']); ?>"></i>
											<?php echo htmlspecialchars($wallet['description']); ?>
										</h5>
										<div>
											<button class="btn btn-warning btn-sm me-2"
													data-bs-toggle="modal"
													data-bs-target="#editWalletModal"
													data-id="<?php echo $wallet['id']; ?>"
													data-description="<?php echo htmlspecialchars($wallet['description']); ?>">
												<i class="bi bi-pencil"></i>
											</button>
											<button class="btn btn-info btn-sm me-2"
													data-bs-toggle="modal"
													data-bs-target="#shareWalletModal"
													data-id="<?php echo $wallet['id']; ?>"
													data-shared="<?php echo htmlspecialchars($wallet['shared_with']); ?>">
												<i class="bi bi-share-fill"></i>
											</button>
											<button class="btn btn-danger btn-sm"
													data-bs-toggle="modal"
													data-bs-target="#deleteWalletModal"
													data-id="<?php echo $wallet['id']; ?>">
												<i class="bi bi-trash"></i>
											</button>
										</div>
									</div>
									<small><?php echo displaySharedInfo($wallet['shared_with']); ?></small>
								</div>
								<!-- Icon indicating whether the wallet is flagged to show on the dashboard -->
								<div class="position-absolute bottom-0 end-0 p-2">
									<?php if ($wallet['show_in_dashboard'] == 1): ?>
										<i class="bi bi-eye-fill"></i>
									<?php else: ?>
										<i class="bi bi-eye-slash"></i>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

            <!-- Section for Wallets Shared with Me -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-0">I portafogli condivisi con me</h4>
                </div>
                <div class="row mt-3">
                    <?php if(!empty($sharedWallets)): ?>
						<?php foreach($sharedWallets as $wallet): ?>
							<div class="col-md-4">
								<div class="card mb-3">
									<div class="card-body">
										<h5 class="card-title mb-0">
											<i class="bi bi-<?php echo htmlspecialchars($wallet['icon']); ?>"></i>
											<?php echo htmlspecialchars($wallet['description']); ?>
										</h5>
										<small>Creato da <?php echo htmlspecialchars($wallet['owner_username']); ?></small>
										<button class="btn btn-sm btn-secondary" 
												data-bs-toggle="modal" 
												data-bs-target="#discardShareModal" 
												data-wallet-id="<?php echo $wallet['id']; ?>">
											<i class="bi bi-x-circle"></i> Rimuovi condivisione
										</button>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p>Nessun portafoglio condiviso con te.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

			<!-- Section for Inactive Wallets -->
			<?php if (!empty($inactiveWallets)): ?>
			<div class="mb-4">
				<div class="d-flex justify-content-between align-items-center flex-wrap">
					<h4 class="mb-0">Portafogli non più attivi</h4>
					<button style="color:white" class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#inactiveWalletsContent" aria-expanded="false" aria-controls="inactiveWalletsContent">
						<i class="bi bi-eye"></i>
					</button>
				</div>
				<div id="inactiveWalletsContent" class="collapse">
					<div class="row mt-3">
						<?php foreach ($inactiveWallets as $wallet): ?>
							<div class="col-md-4">
								<div class="card mb-4">
									<div class="card-body">
										<h5 class="card-title">
                                            <i class="bi bi-<?php echo htmlspecialchars($wallet['icon']); ?>"></i>
                                            <?php echo htmlspecialchars($wallet['description']); ?>
                                        </h5>
                                        <p class="card-text small">
                                            <?php echo displaySharedInfo($wallet['shared_with']); ?>
                                        </p>
										<button class="btn btn-success btn-sm"
												data-bs-toggle="modal"
												data-bs-target="#restoreWalletModal"
												data-id="<?php echo $wallet['id']; ?>">
											<i class="bi bi-arrow-counterclockwise"></i>
										</button>
										<button class="btn btn-danger btn-sm"
												data-bs-toggle="modal"
												data-bs-target="#permanentlyDeleteWalletModal"
												data-id="<?php echo $wallet['id']; ?>">
											<i class="bi bi-trash"></i>
										</button>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
    <?php include 'addWalletModal.php'; ?>
    <?php include 'editWalletModal.php'; ?>
    <?php include 'deleteWalletModal.php'; ?>
    <?php include 'permanentlyDeleteWalletModal.php'; ?>
    <?php include 'restoreWalletModal.php'; ?>
    <?php include 'shareWalletModal.php'; ?>
	<?php include 'discardShareModal.php'; ?>
    <?php include 'navbar.php'; ?>
    <?php include '../footer.php'; ?>
</body>
</html>