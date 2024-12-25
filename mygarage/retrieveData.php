<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$user_id = $_SESSION['id'];

$selectedMonth = date('m');
$selectedYear = date('Y');

// Variabili relative alle date utilizzate nel codice
$oneYearAgo = (new DateTime())->modify('-1 year')->format('Y-m-d');
$today = (new DateTime())->format('Y-m-d');

// Creo una funzione per formattare le date nel formato gg/mm/aaaa
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// Creo una funzione per eseguire le query SQL
function executeQuery($link, $sql, $params, $singleRow = true) {
    $data = [];
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param(...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($singleRow) {
            $data = $result->fetch_assoc();
        } else {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $stmt->close();
    }
    return $data;
}

// Creo una funzione per sommare le spese
function sumExpenses($link, $table, $user_id, $oneYearAgo, $today) {
    $sql = "SELECT SUM(amount) as total FROM $table WHERE user_id = ? AND buying_date BETWEEN ? AND ?";
    $params = ["iss", $user_id, $oneYearAgo, $today];
    $result = executeQuery($link, $sql, $params);
    return $result['total'] ? $result['total'] : 0;
}

// Creo una funzione per calcolare il numero di manutenzioni dell'ultimo anno
function countMaintenancesLastYear($link, $user_id, $oneYearAgo, $today) {
    $sql = "SELECT COUNT(*) as total FROM vehicle_services WHERE user_id = ? AND buying_date BETWEEN ? AND ?";
    $params = ["iss", $user_id, $oneYearAgo, $today];
    $result = executeQuery($link, $sql, $params);
    return $result['total'] ? $result['total'] : 0;
}

// Creo una funzione per calcolare il costo dell'ultima manutenzione
function costOfLastMaintenance($link, $user_id) {
    $sql = "SELECT amount FROM vehicle_services WHERE user_id = ? and amount>0 ORDER BY buying_date DESC LIMIT 1";
    $params = ["i", $user_id];
    $result = executeQuery($link, $sql, $params);
    return $result['amount'] ? $result['amount'] : 0;
}

// Creo una funzione per calcolare la prossima scadenza del bollo
function getNextTaxExpirationDate($month) {
    $currentYear = date('Y');
    $currentMonth = date('n');

    if ($month >= $currentMonth) {
        $year = $currentYear;
    } else {
        $year = $currentYear + 1;
    }

    $date = DateTime::createFromFormat('Y-n-j', "$year-$month-1");

    $date->modify('last day of this month');

    return $date->format('Y-m-d');
}

// Creo una funzione per calcolare la prossima scadenza della revisione
function getNextRevisionExpirationDate($link, $vehicleId, $registrationDate) {
    $sql = "SELECT buying_date FROM vehicle_revisions 
            WHERE vehicle_id = ? 
            ORDER BY buying_date DESC 
            LIMIT 1";
    $params = ["i", $vehicleId];
    $revision = executeQuery($link, $sql, $params);

    if ($revision && isset($revision['buying_date'])) {
        $revisionDate = new DateTime($revision['buying_date']);
        $revisionDate->modify('+2 years');
    } else {
        $revisionDate= new DateTime($registrationDate);
        $revisionDate->modify('+4 years');
    }

    $revisionDate->modify('last day of this month');
    return $revisionDate->format('Y-m-d');
}

// Recupero i veicoli dell'utente corrente
$vehiclesQuery = executeQuery($link, "SELECT id, description, buying_date, registration_date, plate_number, chassis_number, tax_month, revision_month, deleted_at FROM vehicles WHERE user_id = ? ORDER BY id ASC", ["i", $user_id], false);
$vehicles = [];

foreach ($vehiclesQuery as $vehicle) {
    $id = $vehicle['id'];
    $description = $vehicle['description'];
    $buyingDate = $vehicle['buying_date'];
    $registrationDate = $vehicle['registration_date'];
    $plateNumber = $vehicle['plate_number'];
    $chassisNumber = $vehicle['chassis_number'];
    $taxMonth = $vehicle['tax_month'];
    $revisionMonth = $vehicle['revision_month'];
    $deletedAt = $vehicle['deleted_at'];

    $nextTaxExpirationDate = getNextTaxExpirationDate($taxMonth);
    $nextRevisionExpirationDate = getNextRevisionExpirationDate($link, $id, $registrationDate);

    // Calcolo della prossima scadenza dell'assicurazione
    $vehicleInsurance = executeQuery(
        $link,
        "SELECT effective_date FROM vehicle_insurances 
         WHERE user_id = ? AND vehicle_id = ? 
         ORDER BY effective_date DESC LIMIT 1",
        ["ii", $user_id, $id]
    );

    if ($vehicleInsurance && isset($vehicleInsurance['effective_date'])) {
        $effectiveDate = $vehicleInsurance['effective_date'];
        $nextInsuranceExpirationDate = (new DateTime($effectiveDate))
            ->modify('+1 year')
            ->format('Y-m-d');
    } else {
        $nextInsuranceExpirationDate = null;
    }

    $vehicles[] = [
        'id' => $id,
        'description' => $description,
        'buyingDate' => $buyingDate,
        'registrationDate' => $registrationDate,
        'plateNumber' => $plateNumber,
        'chassisNumber' => $chassisNumber,
        'nextTaxExpirationDate' => $nextTaxExpirationDate,
        'nextRevisionExpirationDate' => $nextRevisionExpirationDate,
        'nextInsuranceExpirationDate' => $nextInsuranceExpirationDate,
        'taxMonth' => $taxMonth,
        'revisionMonth' => $revisionMonth,
        'deletedAt' => $deletedAt
    ];
}

// Recupero tutte le manutenzioni
$vehicleServices = executeQuery($link, "SELECT id, vehicle_id, description, amount, buying_date, registered_kilometers, attachment_path FROM vehicle_services WHERE user_id = ? ORDER BY buying_date DESC", ["i", $user_id], false);

// Recupero tutti i pezzi delle manutenzioni
$vehicleServiceParts = executeQuery($link, "SELECT id, service_id, part_name, part_number FROM vehicle_service_parts WHERE user_id = ?", ["i", $user_id], false);

$serviceParts = [];
foreach ($vehicleServiceParts as $part) {
    $serviceId = $part['service_id'];
    if (!isset($serviceParts[$serviceId])) {
        $serviceParts[$serviceId] = [];
    }
    $serviceParts[$serviceId][] = [
        'part_name' => $part['part_name'],
        'part_number' => $part['part_number']
    ];
}

// Recupero tutte le assicurazioni
$vehicleInsurances = executeQuery($link, "SELECT id, vehicle_id, company, amount, buying_date, effective_date FROM vehicle_insurances WHERE user_id = ? ORDER BY buying_date DESC", ["i", $user_id], false);

// Recupero tutte le revisioni
$vehicleRevisions = executeQuery($link, "SELECT id, vehicle_id, amount, buying_date FROM vehicle_revisions WHERE user_id = ? ORDER BY buying_date DESC", ["i", $user_id], false);

// Recupero tutti i bolli
$vehicleTaxes = executeQuery($link, "SELECT id, vehicle_id, amount, buying_date FROM vehicle_taxes WHERE user_id = ? ORDER BY buying_date DESC", ["i", $user_id], false);

// Calcolo le spese totali dell'ultimo anno
$totalVehicleServices = sumExpenses($link, "vehicle_services", $user_id, $oneYearAgo, $today);
$totalVehicleInsurances = sumExpenses($link, "vehicle_insurances", $user_id, $oneYearAgo, $today);
$totalVehicleRevisions = sumExpenses($link, "vehicle_revisions", $user_id, $oneYearAgo, $today);
$totalVehicleTaxes = sumExpenses($link, "vehicle_taxes", $user_id, $oneYearAgo, $today);

// Calcolo le spese totali dell'ultimo anno
$lastYearExpenses = $totalVehicleServices + $totalVehicleInsurances + $totalVehicleRevisions + $totalVehicleTaxes;

// Calcolo le manutenzioni dell'ultimo anno
$lastYearMaintenances = countMaintenancesLastYear($link, $user_id, $oneYearAgo, $today);

// Calculate the cost of the last maintenance
$lastMaintenanceCost = costOfLastMaintenance($link, $user_id);

// Calcolo le prossime scadenze
$nearestTaxExpirationDate = null;
$nearestTaxExpirationVehicle = null;

$nearestServiceExpirationDate = null;
$nearestServiceExpirationVehicle = null;

$nearestRevisionExpirationDate = null;
$nearestRevisionExpirationVehicle = null;

$nearestInsuranceExpirationDate = null;
$nearestInsuranceExpirationVehicle = null;

foreach ($vehicles as $vehicle) {
    // Bollo
    if ($vehicle['nextTaxExpirationDate'] && (is_null($nearestTaxExpirationDate) || $vehicle['nextTaxExpirationDate'] < $nearestTaxExpirationDate)) {
        $nearestTaxExpirationDate = $vehicle['nextTaxExpirationDate'];
        $nearestTaxExpirationVehicle = $vehicle['description'];
    }

    // Revisione
    if ($vehicle['nextRevisionExpirationDate'] && (is_null($nearestRevisionExpirationDate) || $vehicle['nextRevisionExpirationDate'] < $nearestRevisionExpirationDate)) {
        $nearestRevisionExpirationDate = $vehicle['nextRevisionExpirationDate'];
        $nearestRevisionExpirationVehicle = $vehicle['description'];
    }

    // Assicurazione
    if ($vehicle['nextInsuranceExpirationDate'] && (is_null($nearestInsuranceExpirationDate) || $vehicle['nextInsuranceExpirationDate'] < $nearestInsuranceExpirationDate)) {
        $nearestInsuranceExpirationDate = $vehicle['nextInsuranceExpirationDate'];
        $nearestInsuranceExpirationVehicle = $vehicle['description'];
    }
}

foreach ($vehicleServices as $service) {
    $serviceDate = new DateTime($service['buying_date']);
    $nextServiceDate = $serviceDate->modify('+1 year')->format('Y-m-d'); // Assuming a service interval of 1 year

    if (is_null($nearestServiceExpirationDate) || $nextServiceDate < $nearestServiceExpirationDate) {
        $nearestServiceExpirationDate = $nextServiceDate;
        $vehicle = array_filter($vehicles, function($v) use ($service) { return $v['id'] == $service['vehicle_id']; });
        $vehicle = reset($vehicle);
        $nearestServiceExpirationVehicle = $vehicle['description'];
    }
}

// Creo gli array per le spese degli ultimi 12 mesi e per i KM percorsi
$last_12_months = [];
$last_12_insuranceExpenses = [];
$last_12_taxExpenses = [];
$last_12_maintenanceExpenses = [];
$last_12_revisionExpenses = [];
$last_12_travelledKms = [];

$vehicleColors = ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)'];
$vehicleIndex = 0;

// Calcolo i 12 mesi una sola volta e li inverto subito
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $last_12_months[] = date('M Y', strtotime($month));

    // Spese per assicurazioni
    $sql = "SELECT SUM(amount) as total FROM vehicle_insurances WHERE user_id = ? AND DATE_FORMAT(buying_date, '%Y-%m') = ?";
    $insuranceExpense = executeQuery($link, $sql, ["is", $user_id, $month]);
    $last_12_insuranceExpenses[] = $insuranceExpense['total'] ?? 0;

    // Spese per bolli
    $sql = "SELECT SUM(amount) as total FROM vehicle_taxes WHERE user_id = ? AND DATE_FORMAT(buying_date, '%Y-%m') = ?";
    $taxExpense = executeQuery($link, $sql, ["is", $user_id, $month]);
    $last_12_taxExpenses[] = $taxExpense['total'] ?? 0;

    // Spese per manutenzioni
    $sql = "SELECT SUM(amount) as total FROM vehicle_services WHERE user_id = ? AND DATE_FORMAT(buying_date, '%Y-%m') = ?";
    $maintenanceExpense = executeQuery($link, $sql, ["is", $user_id, $month]);
    $last_12_maintenanceExpenses[] = $maintenanceExpense['total'] ?? 0;

    // Spese per revisioni
    $sql = "SELECT SUM(amount) as total FROM vehicle_revisions WHERE user_id = ? AND DATE_FORMAT(buying_date, '%Y-%m') = ?";
    $revisionExpense = executeQuery($link, $sql, ["is", $user_id, $month]);
    $last_12_revisionExpenses[] = $revisionExpense['total'] ?? 0;
}

// Invertiamo i mesi una volta calcolati
$last_12_months = array_reverse($last_12_months);
$last_12_insuranceExpenses = array_reverse($last_12_insuranceExpenses);
$last_12_taxExpenses = array_reverse($last_12_taxExpenses);
$last_12_maintenanceExpenses = array_reverse($last_12_maintenanceExpenses);
$last_12_revisionExpenses = array_reverse($last_12_revisionExpenses);

$vehiclesQuery = executeQuery($link, "SELECT id, description FROM vehicles WHERE user_id = ?", ["i", $user_id], false);

// Calcolo i km percorsi per ciascun veicolo
foreach ($vehiclesQuery as $vehicle) {
    $vehicleId = $vehicle['id'];
    $cumulativeKm = 0;
    $vehicleData = [];

    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));

        // KM percorsi (cumulativi) - vehicle_services table
        $sql = "SELECT MAX(registered_kilometers) as max_km_services FROM vehicle_services WHERE user_id = ? AND vehicle_id = ? AND DATE_FORMAT(buying_date, '%Y-%m') = ?";
        $resultServices = executeQuery($link, $sql, ["iis", $user_id, $vehicleId, $month]);
        $maxKmServices = $resultServices['max_km_services'] ?? 0;

        // Garantire che i chilometri siano cumulativi e non diminuiscano
        if ($maxKmServices > $cumulativeKm) {
            $cumulativeKm = $maxKmServices;
        }

        // Memorizzare i km cumulativi per questo mese
        $vehicleData[] = $cumulativeKm;
    }

    // Non invertire i dati dei veicoli, li aggiungiamo direttamente
    $last_12_travelledKms[] = [
        'vehicle' => $vehicle['description'],
        'kms' => $vehicleData,
        'color' => $vehicleColors[$vehicleIndex % count($vehicleColors)]
    ];

    $vehicleIndex++;
}

// Calcolo le spese totali dell'ultimo anno per ciascuna categoria
$thisYear = date('Y');

$sql = "SELECT SUM(amount) as total FROM vehicle_insurances WHERE user_id = ? AND YEAR(buying_date) = ?";
$thisYearInsuranceExpenses = executeQuery($link, $sql, ["ii", $user_id, $thisYear])['total'] ?? 0;

$sql = "SELECT SUM(amount) as total FROM vehicle_taxes WHERE user_id = ? AND YEAR(buying_date) = ?";
$thisYearTaxExpenses = executeQuery($link, $sql, ["ii", $user_id, $thisYear])['total'] ?? 0;

$sql = "SELECT SUM(amount) as total FROM vehicle_services WHERE user_id = ? AND YEAR(buying_date) = ?";
$thisYearMaintenanceExpenses = executeQuery($link, $sql, ["ii", $user_id, $thisYear])['total'] ?? 0;

$sql = "SELECT SUM(amount) as total FROM vehicle_revisions WHERE user_id = ? AND YEAR(buying_date) = ?";
$thisYearRevisionExpenses = executeQuery($link, $sql, ["ii", $user_id, $thisYear])['total'] ?? 0;

// Inverto gli array per avere i mesi in ordine cronologico
$last_12_months = array_reverse($last_12_months);
$last_12_insuranceExpenses = array_reverse($last_12_insuranceExpenses);
$last_12_taxExpenses = array_reverse($last_12_taxExpenses);
$last_12_maintenanceExpenses = array_reverse($last_12_maintenanceExpenses);
$last_12_revisionExpenses = array_reverse($last_12_revisionExpenses);

?>
