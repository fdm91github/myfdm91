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
                <a class="nav-link" href="incomes.php"><i class="bi bi-download"></i> Entrate</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="recurringExpenses.php"><i class="bi bi-credit-card-fill"></i> Spese ricorrenti</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="estimatedExpenses.php"><i class="bi bi-cart4"></i> Spese stimate</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="extraExpenses.php"><i class="bi bi-bag-heart-fill"></i> Spese extra</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="piggyBank.php"><i class="bi bi-piggy-bank-fill"></i> Salvadanaio</a>
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
