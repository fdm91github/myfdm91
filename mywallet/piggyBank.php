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
    <?php include '../script.php'; ?>
    <link href="../my.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
	<div class="content-wrapper">
		<div class="container mt-5">
            <!-- Filter Section -->
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
                                <label for="searchPiggy">Descrizione:</label>
                                <input type="text" id="searchPiggy" class="form-control" placeholder="Cerca un elemento...">
                            </div>
                            <div class="col-md-6">
                                <form method="GET">
                                    <label for="per_page" class="me-2">Risultati per pagina:</label>
                                    <select name="per_page" id="per_page" class="form-control" onchange="this.form.submit()">
                                        <?php foreach ($perPageOptions as $option): ?>
                                            <option value="<?php echo $option; ?>" <?php echo $perPage == $option ? 'selected' : ''; ?>>
                                                <?php echo $option; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label for="minTotal">Min. Totale:</label>
                                <input type="number" id="minTotal" class="form-control" min="0" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label for="maxTotal">Max. Totale:</label>
                                <input type="number" id="maxTotal" class="form-control" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label for="startDate">Data Inizio:</label>
                                <input type="date" id="startDate" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="endDate">Data Fine:</label>
                                <input type="date" id="endDate" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Top Card: Salvadanaio -->
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h4 class="mb-0">Salvadanaio - Saldo: <?php echo ($totalPiggyBank > 0 ? $totalPiggyBank : '0'); ?>&euro;</h4>
					<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPiggyBankModal">
						<i class="bi bi-plus"></i>
					</button>
				</div>
				<div class="card-body">
                    <!-- Piggy Bank Entries Cards Container -->
                    <div id="piggyEntries">
                        <?php if (!empty($piggyBankEntriesPaginated)): ?>
                            <?php foreach ($piggyBankEntriesPaginated as $entry): ?>
                                <div class="card mb-4">
                                    <!-- Entry header: title and action buttons -->
                                    <div class="card-body d-flex justify-content-between align-items-center">
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
                                    <!-- Entry details -->
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
                    </div>
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
    <!-- Modals -->
    <?php include 'addPiggyBankModal.php'; ?>
    <?php include 'editPiggyBankModal.php'; ?>
    <?php include 'deletePiggyBankModal.php'; ?>
    <?php include 'navbar.php'; ?>
    <?php include '../footer.php'; ?>
    <script>
        $(document).ready(function () {
            function filterPiggyEntries() {
                let searchValue = $("#searchPiggy").val().toLowerCase();
                let minTotal = parseFloat($("#minTotal").val()) || 0;
                let maxTotal = parseFloat($("#maxTotal").val()) || Infinity;
                let startDate = $("#startDate").val() ? new Date($("#startDate").val()) : null;
                let endDate = $("#endDate").val() ? new Date($("#endDate").val()) : null;
                $("#piggyEntries .card").each(function () {
                    let entryName = $(this).find("h5").text().toLowerCase();
                    let totalText = $(this).find("p:contains('Totale:')").text().replace('Totale:', '').replace('€', '').trim();
                    let total = parseFloat(totalText) || 0;
                    let dateText = $(this).find("p:contains('Data:')").text().replace('Data:', '').trim();
                    let parts = dateText.split("/");
                    let entryDate = new Date(parts[2], parts[1] - 1, parts[0]);
                    
                    let matchesSearch = entryName.includes(searchValue);
                    let matchesTotal = total >= minTotal && total <= maxTotal;
                    let matchesDate = true;
                    if(startDate && entryDate < startDate) matchesDate = false;
                    if(endDate && entryDate > endDate) matchesDate = false;
                    
                    $(this).toggle(matchesSearch && matchesTotal && matchesDate);
                });
            }
            $("#searchPiggy, #minTotal, #maxTotal, #startDate, #endDate").on("input change", function () {
                filterPiggyEntries();
                var collapseEl = document.getElementById("filterContent");
                var bsCollapse = new bootstrap.Collapse(collapseEl, {toggle: false});
                bsCollapse.show();
            });
        });
    </script>
</body>
</html>
