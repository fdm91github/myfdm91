<?php
$userId = $_SESSION['id'] ?? null;
$unread = 0;
$lastUnread = [];

if ($userId) {
    // conteggio non lette
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

    // ultime NON lette (es. max 5)
    $sql = "
        SELECT n.id, n.title, n.created_at
        FROM notifications n
        LEFT JOIN notification_reads r
          ON r.notification_id = n.id AND r.user_id = ?
        WHERE r.notification_id IS NULL
        ORDER BY n.created_at DESC
        LIMIT 5
    ";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) $lastUnread[] = $row;
        }
        $stmt->close();
    }
}

$bellIcon = ($unread > 0) ? 'bi-bell-fill' : 'bi-bell';
?>

		
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
		<li class="nav-item dropdown">
		  <a class="nav-link dropdown-toggle position-relative" href="#" id="novitaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
			<i id="bellIcon" class="bi <?= $bellIcon ?>"></i> Novità
			<span id="bellBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?= $unread > 0 ? '' : 'd-none' ?>">
			  <span id="bellCount"><?= (int)$unread ?></span>
			  <span class="visually-hidden">nuove notifiche</span>
			</span>
		  </a>
		  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="novitaDropdown" style="min-width:400px">
			<li class="d-flex justify-content-between align-items-center px-3 py-2">
			  <strong>Ultime notifiche</strong>
			  <?php if ($unread > 0): ?>
				<button class="btn btn-sm btn-outline-primary" id="markAllReadBtn">Segna tutte come lette</button>
			  <?php endif; ?>
			</li>
			<li><hr class="dropdown-divider"></li>

			<?php if (empty($lastUnread)): ?>
			  <li><span class="dropdown-item text-muted">Nessuna notifica non letta</span></li>
			<?php else: ?>
			  <?php foreach ($lastUnread as $n): ?>
				<li class="px-2 py-1 d-flex justify-content-between align-items-start dropdown-notif" data-id="<?= (int)$n['id'] ?>">
				  <button class="btn btn-link btn-sm text-decoration-none markOneReadBtn" title="Segna come letta"><i class="bi bi-envelope-open"></i></button>
				  <a class="dropdown-item small flex-grow-1" href="../news.php#notif<?= (int)$n['id'] ?>">
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
		  <li class="nav-item position-relative">
		    <a class="nav-link" href="../news.php">
		  	<i id="bellIconMobile" class="bi <?= $bellIcon ?>"></i> Novità
		  	<span id="bellBadgeMobile" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?= $unread > 0 ? '' : 'd-none' ?>">
		  	  <span id="bellCountMobile"><?= (int)$unread ?></span>
		  	  <span class="visually-hidden">nuove notifiche</span>
		  	</span>
		    </a>
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

document.addEventListener('DOMContentLoaded', function () {
  const bellBadge       = document.getElementById('bellBadge');
  const bellCount       = document.getElementById('bellCount');
  const bellIcon        = document.getElementById('bellIcon');
  const bellBadgeMobile = document.getElementById('bellBadgeMobile');
  const bellCountMobile = document.getElementById('bellCountMobile');
  const bellIconMobile  = document.getElementById('bellIconMobile');

  function setBadge(count) {
    const n = Math.max(0, parseInt(count || 0, 10));
    if (bellCount) bellCount.textContent = n;
    if (bellCountMobile) bellCountMobile.textContent = n;

    if (n > 0) {
      bellBadge?.classList.remove('d-none');
      bellBadgeMobile?.classList.remove('d-none');
      bellIcon?.classList.remove('bi-bell'); bellIcon?.classList.add('bi-bell-fill');
      bellIconMobile?.classList.remove('bi-bell'); bellIconMobile?.classList.add('bi-bell-fill');
    } else {
      bellBadge?.classList.add('d-none');
      bellBadgeMobile?.classList.add('d-none');
      bellIcon?.classList.remove('bi-bell-fill'); bellIcon?.classList.add('bi-bell');
      bellIconMobile?.classList.remove('bi-bell-fill'); bellIconMobile?.classList.add('bi-bell');
    }
  }
  function decBadge() {
    const n = (parseInt(bellCount?.textContent || '0', 10) - 1);
    setBadge(n);
  }

  // --- Segna TUTTE come lette (già presente, lo lascio coerente con il tuo path) ---
  document.getElementById('markAllReadBtn')?.addEventListener('click', function () {
    fetch('../notifications/mark_all_read.php', {
      method: 'POST',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => r.ok ? Promise.resolve() : Promise.reject())
    .then(() => {
      setBadge(0);
      document.querySelectorAll('.dropdown-notif').forEach(li => li.remove());
      this.classList.add('d-none');
    })
    .catch(() => {});
  });

  // --- Segna UNA come letta (event delegation sul dropdown) ---
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.markOneReadBtn');
    if (!btn) return;

    e.preventDefault();
    const li = btn.closest('.dropdown-notif');
    const id = li?.getAttribute('data-id');
    if (!id) return;

    fetch('../notifications/mark_one_read.php', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'id=' + encodeURIComponent(id)
    })
    .then(r => r.ok ? r.json() : Promise.reject())
    .then(data => {
      if (!data || !data.ok) throw new Error('server');
      li.remove();
      decBadge();
      if ((parseInt(bellCount?.textContent || '0', 10) <= 0)) {
        document.getElementById('markAllReadBtn')?.classList.add('d-none');
      }
    })
    .catch(() => {});
  });
});
</script>