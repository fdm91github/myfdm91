<?php
// Impostazioni di sicurezza per la sessione
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

session_start();
if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#007bff">
    <link rel="icon" href="icon-192x192.png">
    <title>Seleziona un servizio</title>
    <?php include 'script.php' ?>
    <style>
        .card-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .lottie-animation {
            width: 120px;
            height: 120px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div align=center>
	<img src="/images/logo.png">
    </div>
    <div class="container login-container">
	<div class="card">
            <div class="card-header text-center">
		<h4>Seleziona un servizio</h4>
            </div>
            <div class="card-body text-center">
                <h2 class="mt-5">Bentornato!</h2>
                <p class="lead">Scegli cosa vuoi visualizzare:</p>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <a href="mywallet">
                                <div class="lottie-animation" data-animation-path="cards/wallet.json"></div>
                                <div class="card-body">
                                    <p class="card-text">MyWallet</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <a href="mygarage">
                                <div class="lottie-animation" data-animation-path="cards/garage.json"></div>
                                <div class="card-body">
                                    <p class="card-text">MyGarage</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <a href="mysettings">
                                <div class="lottie-animation" data-animation-path="cards/settings.json"></div>
                                <div class="card-body">
                                    <p class="card-text">MySettings</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <p class="mt-3"><a href="logout.php">Logout</a></p>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var animations = document.querySelectorAll('.lottie-animation');
            animations.forEach(function(animation) {
                var animationPath = animation.getAttribute('data-animation-path');
                lottie.loadAnimation({
                    container: animation,
                    renderer: 'svg',
                    loop: true,
                    autoplay: true,
                    path: animationPath
                });
            });
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>

