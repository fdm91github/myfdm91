<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';

// Pagination settings
$perPageOptions = [15, 25, 50, 100];
$perPage = isset($_GET['per_page']) && in_array($_GET['per_page'], $perPageOptions) ? $_GET['per_page'] : 15;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Retrieve paginated data
$totalEntries = count($piggyBankEntries);
$totalPages = ceil($totalEntries / $perPage);
$piggyBankEntriesPaginated = array_slice($piggyBankEntries, $offset, $perPage);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Salvadanaio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Centralized updated scripts (Bootstrap 5.3.3, jQuery, Popper, etc.) -->
    <?php include '../script.php'; ?>
    <!-- Bootstrap Icons -->
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
					<h4 class="mb-0">Salvadanaio</h4>
					<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPiggyBankModal">
						<i class="bi bi-plus"></i>
					</button>
				</div>
				<div class="card-body">
					<div class="d-flex justify-content-between mb-3">
						<form method="GET" class="d-flex align-items-center">
							<label for="per_page" class="me-2">Mostra</label>
							<select name="per_page" id="per_page" class="form-control" onchange="this.form.submit()">
								<?php foreach ($perPageOptions as $option): ?>
									<option value="<?php echo $option; ?>" <?php echo $perPage == $option ? 'selected' : ''; ?>>
										<?php echo $option; ?>
									</option>
								<?php endforeach; ?>
							</select>
							<span class="ms-2">voci per pagina</span>
						</form>
					</div>
					<!-- Table-to-Card Transformation for Piggy Bank Entries -->
					<?php if (!empty($piggyBankEntriesPaginated)): ?>
						<?php foreach ($piggyBankEntriesPaginated as $entry): ?>
							<div class="card mb-3">
								<div class="card-header d-flex justify-content-between align-items-center">
									<h5 class="mb-0"><?php echo htmlspecialchars($entry['name']); ?></h5>
									<div>
										<button class="btn btn-warning btn-sm me-2"
											data-bs-toggle="modal"
											data-bs-target="#editPiggyBankModal"
											data-id="<?php echo $entry['id']; ?>"
											data-name="<?php echo htmlspecialchars($entry['name']); ?>"
											data-amount="<?php echo htmlspecialchars($entry['amount']); ?>"
											data-date="<?php echo htmlspecialchars($entry['added_date']); ?>">
											<i class="bi bi-pencil"></i>
										</button>
										<button class="btn btn-danger btn-sm"
											data-bs-toggle="modal"
											data-bs-target="#deletePiggyBankModal"
											data-id="<?php echo $entry['id']; ?>">
											<i class="bi bi-trash"></i>
										</button>
									</div>
								</div>
								<div class="card-body">
									<p><strong>Totale:</strong> <?php echo htmlspecialchars($entry['amount']); ?> &euro;</p>
									<p><strong>Data:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($entry['added_date']))); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else: ?>
						<div class="alert alert-info" role="alert">
							Nessun elemento nel salvadanaio trovato.
						</div>
					<?php endif; ?>
					<!-- Pagination -->
					<nav>
						<ul class="pagination justify-content-center">
							<?php for ($i = 1; $i <= $totalPages; $i++): ?>
								<li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
									<a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $perPage; ?>"><?php echo $i; ?></a>
								</li>
							<?php endfor; ?>
						</ul>
					</nav>
				</div>
			</div>
		</div>
	</div>
    <?php include 'addPiggyBankModal.php'; ?>
    <?php include 'editPiggyBankModal.php'; ?>
    <?php include 'deletePiggyBankModal.php'; ?>
    <?php include 'navbar.php'; ?>
    <?php include '../footer.php'; ?>
</body>
</html>
