<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$current_password_err = $new_password_err = $confirm_password_err = "";
$current_password = $new_password = $confirm_password = "";

$user_id = $_SESSION['id'];
$profile_err = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Convalido e aggiorno le informazioni del profilo
    $name = trim($_POST["name"]);
    $surname = trim($_POST["surname"]);
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $salary_date = trim($_POST["salary_date"]);

    if (empty($name) || empty($surname) || empty($email) || empty($username)) {
        $profile_err = "Tutti i campi del profilo sono obbligatori.";
    } else {
        $sql = "UPDATE users SET name = ?, surname = ?, email = ?, username = ?, salary_date = ? WHERE id = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("sssssi", $name, $surname, $email, $username, $salary_date, $user_id);
            if ($stmt->execute()) {
                $success_message = "Profilo aggiornato con successo.";
            } else {
                $profile_err = "Qualcosa è andato storto. Riprova più tardi.";
            }
            $stmt->close();
        }
    }

    // Convalido e aggiorno la password solo se i campi sono compilati
    if (!empty(trim($_POST["current_password"])) || !empty(trim($_POST["new_password"])) || !empty(trim($_POST["confirm_password"]))) {
        if (empty(trim($_POST["current_password"]))) {
            $current_password_err = "Inserisci la tua password attuale.";
        } else {
            $current_password = trim($_POST["current_password"]);
        }

        if (empty(trim($_POST["new_password"]))) {
            $new_password_err = "Inserisci la nuova password.";
        } elseif (strlen(trim($_POST["new_password"])) < 8) {
            $new_password_err = "La password deve contenere almeno 8 caratteri.";
        } else {
            $new_password = trim($_POST["new_password"]);
        }

        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Inserisci la nuova password ancora una volta.";
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if (empty($new_password_err) && ($new_password != $confirm_password)) {
                $confirm_password_err = "Le password non corrispondono.";
            }
        }

        if (empty($current_password_err) && empty($new_password_err) && empty($confirm_password_err)) {
            $sql = "SELECT password FROM users WHERE id = ?";
            if ($stmt = $link->prepare($sql)) {
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows == 1) {
                        $stmt->bind_result($hashed_password);
                        if ($stmt->fetch()) {
                            if (password_verify($current_password, $hashed_password)) {
                                $sql = "UPDATE users SET password = ? WHERE id = ?";
                                if ($stmt = $link->prepare($sql)) {
                                    $stmt->bind_param("si", $param_password, $user_id);
                                    $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                                    if ($stmt->execute()) {
                                        $success_message = "Password aggiornata con successo.";
                                    } else {
                                        $profile_err = "Qualcosa è andato storto. Riprova più tardi.";
                                    }
                                }
                            } else {
                                $current_password_err = "La password attuale inserita non è corretta.";
                            }
                        }
                    } else {
                        $profile_err = "Utente non trovato. Riprova o contattami all'indirizzo admin@fdm91.net";
                    }
                } else {
                    $profile_err = "Qualcosa è andato storto. Riprova più tardi.";
                }
                $stmt->close();
            }
        }
    }

    // Verifica se ci sono errori prima di mostrare il messaggio di successo
    if (empty($profile_err) && empty($current_password_err) && empty($new_password_err) && empty($confirm_password_err)) {
        $success_message = "Modifica del profilo avvenuta con successo. Sarai reindirizzato alla pagina di login in pochi istanti.";
        echo "<script>
            setTimeout(function(){
                window.location.href = '../logout.php';
            }, 3000);
            </script>";
    }

    $link->close();
} else {
    // Precarico le informazioni del profilo
    $sql = "SELECT name, surname, email, username, salary_date FROM users WHERE id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $stmt->bind_result($name, $surname, $email, $username, $salary_date);
            $stmt->fetch();
        }
        $stmt->close();
    }
    $link->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Profilo utente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
	<!-- Custom CSS -->
	<link href="../my.css" rel="stylesheet">
	<style>
        body {
            padding-top: 56px;
        }
        .form-container {
            max-width: 500px;
            margin: 50px auto;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container form-container">
        <h2 class="text-center">Il mio Profilo</h2>
        <p>Modifica le tue informazioni del profilo e la password.</p>
        <?php
        if (!empty($profile_err)) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($profile_err) . '</div>';
        } elseif (!empty($success_message)) {
            echo '<div class="alert alert-success" id="success-message">' . htmlspecialchars($success_message) . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="name">Nome</label>
                <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="form-group">
                <label for="surname">Cognome</label>
                <input type="text" name="surname" id="surname" class="form-control" value="<?php echo htmlspecialchars($surname); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Nome utente</label>
                <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" readonly required>
            </div>
            <div class="form-group">
                <label for="salary_date">Data di accredito dello stipendio</label>
                <input type="number" name="salary_date" id="salary_date" class="form-control" value="<?php echo htmlspecialchars($salary_date); ?>">
            </div>
            <hr>
            <div class="form-group">
                <p>Se vuoi aggiornare la password, compila anche i campi sottostanti, altrimenti lasciali vuoti.</p>
                <label for="current_password">Password corrente</label>
                <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Inserisci qui la tua password attuale">
                <span class="text-danger"><?php echo $current_password_err; ?></span>
            </div>
            <div class="form-group">
                <label for="new_password">Nuova password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Inserisci la nuova password">
                <span class="text-danger"><?php echo $new_password_err; ?></span>
            </div>
            <div class="form-group">
                <label for="confirm_password">Conferma password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Conferma la nuova password">
                <span class="text-danger"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Modifica Profilo</button>
            </div>
        </form>
        <p class="text-center">Hai cambiato idea? <a href="../">Torna alla Dashboard</a></p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
