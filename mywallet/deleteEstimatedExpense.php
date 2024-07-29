<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once 'config.php';

$error_message = '';
$success_message = '';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $user_id = $_SESSION['id'];

    $sql = "DELETE FROM estimated_expenses WHERE id = ? AND user_id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ii", $id, $user_id);

        if ($stmt->execute()) {
            $success_message = "Estimated expense deleted successfully. Redirecting to dashboard...";
            header("refresh:3; url=dashboard.php");
        } else {
            $error_message = "Something went wrong. Please try again later.";
        }
        $stmt->close();
    } else {
        $error_message = "Something went wrong. Please try again later.";
    }
    $link->close();
} else {
    $error_message = "Invalid request. No expense ID provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Estimated Expense</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

