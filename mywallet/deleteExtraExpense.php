<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$error_message = '';
$success_message = '';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $user_id = $_SESSION['id'];

    $sql = "DELETE FROM wallet_extra_expenses WHERE id = ? AND user_id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ii", $id, $user_id);

        if ($stmt->execute()) {
            $success_message = "Spesa eliminata con successo.";
            header("refresh:3; url=dashboard.php");
        } else {
            $error_message = "Qualcosa è andato storto. Riprova più tardi.";
        }
        $stmt->close();
    } else {
        $error_message = "Qualcosa è andato storto. Riprova più tardi.";
    }
    $link->close();
} else {
    $error_message = "Invalid request. No expense ID provided.";
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Delete Recurring Expense</title>
    <!-- Custom CSS -->
    <style>
        body {
            padding-top: 56px;
        }
        .message-container {
            max-width: 500px;
            margin: 50px auto;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <a class="navbar-brand" href="#">Wallet Manager</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Message Container -->
    <div class="container message-container">
        <?php
        if (!empty($error_message)) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
        } elseif (!empty($success_message)) {
            echo '<div class="alert alert-success">' . htmlspecialchars($success_message) . '</div>';
        }
        ?>
        <p class="text-center"><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>

