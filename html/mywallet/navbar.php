<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="../">
      <img src="/images/logo.png" alt="Logo" height="30">
    </a>
    <!-- Toggler for offcanvas sidebar on small screens -->
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
          <a class="nav-link" href="incomes.php"><i class="bi bi-download"></i> Entrate</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="recurringExpenses.php"><i class="bi bi-credit-card-fill"></i> Spese ricorrenti</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="estimatedExpenses.php"><i class="bi bi-cart4"></i> Spese stimate</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="wallets.php"><i class="bi bi-wallet-fill"></i> Gestione Portafogli</a>
        </li>
		<li class="nav-item">
          <a class="nav-link" href="walletData.php"><i class="bi bi-cash-coin"></i> Portafogli</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="piggyBank.php"><i class="bi bi-piggy-bank-fill"></i> Salvadanaio</a>
        </li>
        <li class="nav-item">
	  <a class="nav-link" href="../logout.php"><i class="bi bi-door-closed-fill"></i> Esci</a>
	</li>
	<?php
	$isAdminNews = isset($_SESSION['username']) && $_SESSION['username'] === 'fdellamorte';

	// Recupera ultime 3 novità
	$novita = [];
	$sql = "SELECT id, titolo, created_at FROM novita ORDER BY created_at DESC LIMIT 3";
	if ($res = $link->query($sql)) {
	    while ($row = $res->fetch_assoc()) {
	        $novita[] = $row;
	    }
	}
	?>
	<li class="nav-item dropdown">
	  <a class="nav-link dropdown-toggle position-relative" href="#" id="novitaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
	    <i class="bi bi-bell"></i> Novità
	  </a>
	  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="novitaDropdown" style="min-width:300px">
	    <li><h6 class="dropdown-header">Ultime novità</h6></li>
	    <?php if (empty($novita)): ?>
	      <li><span class="dropdown-item text-muted">Nessuna novità</span></li>
	    <?php else: ?>
	      <?php foreach ($novita as $n): ?>
	        <li>
	          <a class="dropdown-item small" href="news.php#n<?= $n['id'] ?>">
	            <?= htmlspecialchars($n['titolo'], ENT_QUOTES, 'UTF-8') ?><br>
	            <small class="text-muted"><?= date('d/m/Y', strtotime($n['created_at'])) ?></small>
	          </a>
	        </li>
	      <?php endforeach; ?>
	    <?php endif; ?>
	    <li><hr class="dropdown-divider"></li>
	    <li><a class="dropdown-item text-center" href="news.php">Vedi tutte</a></li>
	    <?php if ($isAdminNews): ?>
	      <li><a class="dropdown-item text-center" href="news.php#add">➕ Aggiungi</a></li>
	    <?php endif; ?>
	  </ul>
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
            <a class="nav-link" href="incomes.php"><i class="bi bi-download"></i> Entrate</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="recurringExpenses.php"><i class="bi bi-credit-card-fill"></i> Spese ricorrenti</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="estimatedExpenses.php"><i class="bi bi-cart4"></i> Spese stimate</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="wallets.php"><i class="bi bi-wallet-fill"></i> Gestione Portafogli</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="walletData.php"><i class="bi bi-cash-coin"></i> Portafogli</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="piggyBank.php"><i class="bi bi-piggy-bank-fill"></i> Salvadanaio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../logout.php"><i class="bi bi-door-closed-fill"></i> Esci</a>
	  </li>
	  <li class="nav-item">
	    <a class="nav-link" href="news.php"><i class="bi bi-bell"></i> Novità</a>
	  </li>
	  <?php if ($isAdminNews): ?>
	  <li class="nav-item">
	    <a class="nav-link" href="news.php#add"><i class="bi bi-plus-circle"></i> Aggiungi novità</a>
	  </li>
	  <?php endif; ?>

        </ul>
      </div>
    </div>
  </div>
</nav>

<!-- Custom CSS to adjust offcanvas -->
<style>
  .custom-offcanvas {
    width: auto;
    max-width: 250px;
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
document.addEventListener('DOMContentLoaded', function() {
  // Get the current path (e.g., incomes.php)
  var currentPath = window.location.pathname.split("/").pop();
  // Get all nav-link elements
  var navLinks = document.querySelectorAll('.navbar-nav .nav-link');
  // Loop through the nav links and check if the href matches the current path
  navLinks.forEach(function(link) {
    var linkPath = link.getAttribute('href');
    if (linkPath === currentPath || (linkPath === '.' && currentPath === '')) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });
});
</script>
