<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="../">
      <img src="/images/logo.png" alt="Logo">
    </a>
    <!-- This toggler will trigger the offcanvas sidebar on small screens -->
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
      aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Regular navbar visible on large screens -->
    <div class="collapse navbar-collapse d-none d-lg-block">
      <ul class="navbar-nav ms-auto">
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

    <!-- Offcanvas sidebar for small screens -->
    <div class="offcanvas offcanvas-end d-lg-none text-bg-dark custom-offcanvas" tabindex="-1" id="offcanvasNavbar"
      aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
          aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <ul class="navbar-nav">
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
    </div>
  </div>
</nav>

<!-- Custom CSS to adjust offcanvas -->
<style>
  .custom-offcanvas {
    width: auto;
    max-width: 220px;
  }

  .custom-offcanvas .nav-link {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.5rem;
    text-align: right;
  }

  .custom-offcanvas .nav-link > i {
    order: 2;
  }
  
  /* Impostazioni della navbar/sidebar */
  @media (max-width: 768px) {
	.navbar-toggler {
	  font-size: 1rem;
	  padding: 0.75rem 1.25rem;
	}

	.offcanvas .nav-link {
	  font-size: 1.25rem;
	  padding: 0.75rem 1.25rem;
	}
}
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Get the current path (e.g., vehicles.php)
    var currentPath = window.location.pathname.split("/").pop();

    // Get all nav-link elements from both navbar and offcanvas
    var navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    navLinks.forEach(function (link) {
      var linkPath = link.getAttribute('href');
      if (linkPath === currentPath || (linkPath === '.' && currentPath === '')) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });
  });
</script>
