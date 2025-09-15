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
		$userId = $_SESSION['id'] ?? null;
		$unread = 0;
		
		if ($userId) {
		  // non lette = tutte le notifications NON presenti nella tabella delle letture per quell'utente
		  $sql = "
			SELECT COUNT(*) AS unread
			FROM notifications n
			LEFT JOIN notification_reads r
			  ON r.notification_id = n.id AND r.user_id = ?
			WHERE r.notification_id IS NULL
		  ";
		  if ($stmt = $link->prepare($sql)) {
			$stmt->bind_param("i", $userId);
			$stmt->execute();
			$stmt->bind_result($unread);
			$stmt->fetch();
			$stmt->close();
		  }
		}
		
		// Prendi le ultime 3 per mostrare il riepilogo
		$last3 = [];
		if ($userId) {
		  $sql = "SELECT id, title, created_at FROM notifications ORDER BY created_at DESC LIMIT 3";
		  if ($res = $link->query($sql)) {
			while ($row = $res->fetch_assoc()) $last3[] = $row;
		  }
		}
		
		$bellIcon = ($unread > 0) ? 'bi-bell-fill' : 'bi-bell';
		?>
		<li class="nav-item dropdown">
		  <a class="nav-link dropdown-toggle position-relative" href="#" id="novitaDropdown" role="button"
			 data-bs-toggle="dropdown" aria-expanded="false">
			<i id="bellIcon" class="bi <?= $bellIcon ?>"></i> Novità
			<span id="bellBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?= $unread > 0 ? '' : 'd-none' ?>">
			  <?= (int)$unread ?>
			  <span class="visually-hidden">nuove notifiche</span>
			</span>
		  </a>
			<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="novitaDropdown" style="min-width:320px">
				<li class="d-flex justify-content-between align-items-center px-3 py-2">
					<strong>Ultime notifiche</strong>
					<?php if ($unread > 0): ?>
					<button class="btn btn-sm btn-outline-primary" id="markAllReadBtn">Segna tutte come lette</button>
					<?php endif; ?>
				</li>
				<li><hr class="dropdown-divider"></li>
				  <?php if (empty($last3)): ?>
				<li><span class="dropdown-item text-muted">Nessuna notifica</span></li>
					<?php else: ?>
						<?php foreach ($last3 as $n): ?>
							<li>
								<a class="dropdown-item small" href="../news.php#notif<?= (int)$n['id'] ?>">
									<?= htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8') ?><br>
								<small class="text-muted"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></small>
								</a>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
					<li><hr class="dropdown-divider"></li>
					<li><a class="dropdown-item text-center" href="../news.php">Vedi tutte</a></li>
			</ul>
		</li>
		<script>
		document.addEventListener('DOMContentLoaded', function () {
		  // Segna tutte come lette
		  document.getElementById('markAllReadBtn')?.addEventListener('click', function () {
			fetch('../notifications_mark_all_read.php', { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'} })
			  .then(r => r.ok ? Promise.resolve() : Promise.reject())
			  .then(() => {
				// UI: azzera badge + icona vuota
				const badge = document.getElementById('bellBadge');
				const icon  = document.getElementById('bellIcon');
				if (badge) { badge.classList.add('d-none'); badge.textContent = ''; }
				if (icon) { icon.classList.remove('bi-bell-fill'); icon.classList.add('bi-bell'); }
				// Nascondi bottone
				document.getElementById('markAllReadBtn')?.classList.add('d-none');
			  })
			  .catch(() => {});
		  });
		});
		</script>
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
	    <a class="nav-link" href="../news.php"><i class="bi bi-bell"></i> Novità</a>
	  </li>
	  <?php if ($isAdminNews): ?>
	  <li class="nav-item">
	    <a class="nav-link" href="../news.php#add"><i class="bi bi-plus-circle"></i> Aggiungi novità</a>
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
