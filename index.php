<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#007bff">
    <link rel="icon" href="icon-192x192.png">
    <title>Seleziona un servizio</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lottie Web Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.7.6/lottie.min.js"></script>
    <!-- Custom CSS -->
    <link href="my.css" rel="stylesheet">
    <style>
        .card-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .lottie-animation {
            width: 100px;
            height: 100px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="card">
            <div class="card-header text-center">
                <h4>Seleziona un servizio</h4>
            </div>
            <div class="card-body text-center">
                <h2 class="mt-5">Bentornato, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
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
	
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
</body>
</html>

