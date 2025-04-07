<body class="d-flex flex-column m-0">
    <div class="flex-grow-1">
    </div>

    <footer class="footer bg-dark text-light text-center border-top border-dark py-2">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> MyFDM91. Tutti i diritti riservati.
            <?php
                $version = trim(@file_get_contents("VERSION"));
                if ($version) {
                    echo ' <a href="https://github.com/fdm91github/myfdm91/releases/tag/' . $version . '">Versione ' . $version . '</a>';
                }
            ?>
            </p>
        </div>
    </footer>
</body>

