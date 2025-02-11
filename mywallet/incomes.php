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
$totalEntries = count($wallet_incomes);
$totalPages = ceil($totalEntries / $perPage);
$wallet_incomes_paginated = array_slice($wallet_incomes, $offset, $perPage);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Entrate</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../my.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="mb-0">Entrate</h4>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addIncomeModal">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <form method="GET" class="form-inline">
                        <label for="per_page" class="mr-2">Mostra</label>
                        <select name="per_page" id="per_page" class="form-control" onchange="this.form.submit()">
                            <?php foreach ($perPageOptions as $option): ?>
                                <option value="<?php echo $option; ?>" <?php echo $perPage == $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="ml-2">voci per pagina</span>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Descrizione</th>
                                <th>Totale</th>
                                <th>Data di aggiunta</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wallet_incomes_paginated as $entry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($entry['name']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['amount']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($entry['added_date']))); ?></td>
                                    <td>
					<button class="btn btn-warning btn-sm"
					    data-toggle="modal"
					    data-target="#editIncomeModal"
					    data-id="<?php echo $entry['id']; ?>"
					    data-name="<?php echo htmlspecialchars($entry['name']); ?>"
					    data-amount="<?php echo htmlspecialchars($entry['amount']); ?>"
					    data-date="<?php echo htmlspecialchars($entry['added_date']); ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteIncomeModal" data-id="<?php echo $entry['id']; ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
    <?php include 'addIncomeModal.php'; ?>
    <?php include 'editIncomeModal.php'; ?>
    <?php include 'deleteIncomeModal.php'; ?>
    <?php include 'navbar.php'; ?>
    <?php include '../footer.php'; ?>
</body>
</html>

