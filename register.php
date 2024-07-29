<?php
session_start();

// Controllo che l'utente non sia già loggato, in tal caso effettuo redirect sulla dashboard
if (isset($_SESSION['username'])) {
    header("location: dashboard.php");
    exit;
}

require_once 'config.php';

$error_message = '';
$success_message = '';

// Genero una verifica semplice per ridurre registrazioni dei bot 
$verification_num1 = rand(1, 10);
$verification_num2 = rand(1, 10);
$verification_answer = $verification_num1 + $verification_num2;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_answer = $_POST['verification_answer'];

    // Convalido i dati
    if ($password !== $confirm_password) {
        $error_message = "Le password non corrispondono.";
    } elseif ($user_answer != $_POST['correct_answer']) {
        $error_message = "Sei proprio sicuro di essere un umano?";
    } else {
        // Controllo se esistono già la username o l'email
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("ss", $username, $email);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $error_message = "Il nome utente o l'indirizzo email risultano già registrati :(";
                } else {
                    // Inserisco il nuovo utente nel database
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO users (name, surname, email, username, password) VALUES (?, ?, ?, ?, ?)";
                    if ($stmt = $link->prepare($sql)) {
                        $stmt->bind_param("sssss", $name, $surname, $email, $username, $hashed_password);
                        if ($stmt->execute()) {
                            $success_message = "Ti ho registrato! Ora puoi accedere :)";
                        } else {
                            $error_message = "Qualcosa è andato storto. Riprova più tardi.";
                        }
                    }
                }
            } else {
                $error_message = "Qualcosa è andato storto. Riprova più tardi.";
            }
            $stmt->close();
        }
    }
    $link->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body, html {
            height: 100%;
            background-color: #f8f9fa;
        }
        .register-form {
            max-width: 500px;
            margin: 50px auto;
            padding: 15px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <a class="navbar-brand" href="#">Wallet Manager</a>
    </nav>

    <!-- Register Form -->
    <div class="container">
        <div class="register-form">
            <div class="card">
                <div class="card-header text-center">
                    <h2>Registrati su MyFDM91</h2>
                </div>
                <div class="card-body">
                    <?php
                    if (!empty($error_message)) {
                        echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
                    } elseif (!empty($success_message)) {
                        echo '<div class="alert alert-success">' . htmlspecialchars($success_message) . '</div>';
                    }
                    ?>
                    <form action="register.php" method="post">
                        <div class="form-group">
                            <label for="name">Nome</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Nome" required>
                        </div>
                        <div class="form-group">
                            <label for="surname">Cognome</label>
                            <input type="text" name="surname" id="surname" class="form-control" placeholder="Cognome" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Conferma Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Conferma Password" required>
                        </div>
                        <div class="form-group">
                            <label for="verification_question">Verifica di essere un umano: quanto fa <?php echo $verification_num1 . " + " . $verification_num2; ?>?</label>
                            <input type="number" name="verification_answer" id="verification_question" class="form-control" placeholder="La tua risposta umana" required>
                        </div>
                        <input type="hidden" name="correct_answer" value="<?php echo $verification_answer; ?>">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Register</button>
                        </div>
                        <p class="text-center">Hai gi&agrave un account? <a href="login.php">Accedi qui</a>.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
