<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Salvadanaio</title>
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
</head>
<body>
    <div class="container mt-5">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="mb-0">Salvadanaio</h4>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addPiggyBankModal">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="card-body">
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
                            <?php foreach ($piggyBankEntries as $entry): ?>
                                <tr>
									<td><?php echo htmlspecialchars($entry['name']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['amount']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($entry['added_date']))); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm"
                                                data-toggle="modal"
                                                data-target="#editPiggyBankModal"
                                                data-id="<?php echo $entry['id']; ?>"
												data-name="<?php echo htmlspecialchars($entry['name']); ?>"
                                                data-amount="<?php echo htmlspecialchars($entry['amount']); ?>"
                                                data-date="<?php echo htmlspecialchars($entry['added_date']); ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                                data-toggle="modal"
                                                data-target="#deletePiggyBankModal"
                                                data-id="<?php echo $entry['id']; ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
