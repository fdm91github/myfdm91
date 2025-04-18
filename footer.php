<body class="d-flex flex-column m-0">
    <div class="flex-grow-1">
    </div>

    <footer class="footer bg-dark text-light text-center border-top border-dark py-2">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> MyFDM91. Tutti i diritti riservati
            <?php
                // Percorso assoluto relativo alla root del progetto
                $versionFile = $_SERVER['DOCUMENT_ROOT'] . "/VERSION";
                $version = trim(@file_get_contents($versionFile));

                if ($version) {
                    // Link alla release GitHub
                    $githubUrl = "https://github.com/fdm91github/myfdm91/releases/" . $version;
                    echo ' - v' . $version . ' (<a style="color:white;" href="' . $githubUrl . '" target="_blank" rel="noopener">Note di rilascio</a>)';
                }
            ?>
            </p>
        </div>
    </footer>
</body>

