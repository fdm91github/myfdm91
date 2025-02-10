<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="../"><img src="/images/logo.png"></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="."><i class="bi bi-house-door-fill"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="vehicles.php"><i class="bi bi-car-front-fill"></i> I miei veicoli</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="services.php"><i class="bi bi-tools"></i> Manutenzioni</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="insurances.php"><i class="bi bi-bank2"></i> Assicurazioni</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="revisions.php"><i class="bi bi-clipboard2-check-fill"></i> Revisioni</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="taxes.php"><i class="bi bi-cash-coin"></i> Bolli</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php"><i class="bi bi-door-closed-fill"></i> Esci</a>
            </li>
        </ul>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the current path (e.g., /incomes.php)
        var currentPath = window.location.pathname.split("/").pop();

        // Get all nav-link elements
        var navLinks = document.querySelectorAll('.navbar-nav .nav-link');

        // Loop through the nav links and check if the href matches the current path
        navLinks.forEach(function(link) {
            var linkPath = link.getAttribute('href');

            // If the href matches the current path, add the 'active' class
            if (linkPath === currentPath || (linkPath === '.' && currentPath === '')) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    });
</script>
