<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}
require_once '../config.php';

$isAdminNews = ($_SESSION['username'] ?? '') === 'fdellamorte';

// CSRF
if (empty($_SESSION['csrf_news'])) {
    $_SESSION['csrf_news'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = null;

// --- Handle POST (insert / update / delete) ---
if ($isAdminNews && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf_news'], $_POST['csrf'])) {
        $errors[] = "Token di sicurezza non valido.";
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $titolo = trim($_POST['titolo'] ?? '');
            $contenuto = trim($_POST['contenuto'] ?? '');
            if ($titolo === '' || mb_strlen($titolo) > 150) $errors[] = "Titolo non valido.";
            if ($contenuto === '') $errors[] = "Contenuto obbligatorio.";
            if (!$errors) {
                $stmt = $link->prepare("INSERT INTO novita (titolo, contenuto, created_by) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $titolo, $contenuto, $_SESSION['username']);
                $stmt->execute();
                $stmt->close();
                $success = "Novità aggiunta.";
            }
        }

        if ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $titolo = trim($_POST['titolo'] ?? '');
            $contenuto = trim($_POST['contenuto'] ?? '');
            if ($id <= 0) $errors[] = "ID non valido.";
            if ($titolo === '' || mb_strlen($titolo) > 150) $errors[] = "Titolo non valido.";
            if ($contenuto === '') $errors[] = "Contenuto obbligatorio.";
            if (!$errors) {
                $stmt = $link->prepare("UPDATE novita SET titolo=?, contenuto=? WHERE id=?");
                $stmt->bind_param("ssi", $titolo, $contenuto, $id);
                $stmt->execute();
                $stmt->close();
                $success = "Novità aggiornata.";
            }
        }

        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) $errors[] = "ID non valido.";
            if (!$errors) {
                $stmt = $link->prepare("DELETE FROM novita WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                $success = "Novità eliminata.";
            }
        }
        $_SESSION['csrf_news'] = bin2hex(random_bytes(32)); // rigenera token
    }
}

// --- Fetch tutte le novità ---
$novita = [];
$res = $link->query("SELECT id, titolo, contenuto, created_at, created_by FROM novita ORDER BY created_at DESC");
if ($res) while ($row = $res->fetch_assoc()) $novita[] = $row;
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Novità</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php include '../script.php'; ?>
  <link href="../my.css" rel="stylesheet">
</head>
<body>
<div class="content-wrapper container mt-5">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Novità</h3>
    <?php if ($isAdminNews): ?>
      <a href="#add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Aggiungi</a>
    <?php endif; ?>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <?php if ($isAdminNews): ?>
  <div id="add" class="card mb-4">
    <div class="card-header"><strong>Nuova novità</strong></div>
    <div class="card-body">
      <form method="POST" action="#add">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_news'] ?>">
        <input type="hidden" name="action" value="add">
        <div class="mb-3">
          <label for="titolo" class="form-label">Titolo</label>
          <input type="text" name="titolo" id="titolo" class="form-control" maxlength="150" required>
        </div>
        <div class="mb-3">
          <label for="contenuto" class="form-label">Contenuto</label>
          <textarea name="contenuto" id="contenuto" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Salva</button>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header"><strong>Elenco</strong></div>
    <div class="card-body">
      <?php if (empty($novita)): ?>
        <p>Nessuna novità.</p>
      <?php else: ?>
        <?php foreach ($novita as $n): ?>
          <div id="n<?= $n['id'] ?>" class="mb-5">
            <button onclick="history.back()" class="btn btn-sm btn-outline-secondary mb-2">
              ⬅ Indietro
            </button>
            <h5><?= htmlspecialchars($n['titolo']) ?></h5>
            <div class="small mb-2" style="color:grey">
              Pubblicato il <?= date('d/m/Y H:i', strtotime($n['created_at'])) ?> da <?= htmlspecialchars($n['created_by']) ?>
            </div>
            <div class="mb-2"><?= nl2br(htmlspecialchars($n['contenuto'])) ?></div>

            <?php if ($isAdminNews): ?>
              <!-- Pulsanti admin -->
              <div class="d-flex gap-2">
                <!-- Modifica -->
                <button class="btn btn-sm btn-warning" data-bs-toggle="collapse" data-bs-target="#editForm<?= $n['id'] ?>">Modifica</button>
                <!-- Elimina -->
                <form method="POST" onsubmit="return confirm('Eliminare questa novità?')">
                  <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_news'] ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $n['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger">Elimina</button>
                </form>
              </div>

              <!-- Form modifica collapsible -->
              <div class="collapse mt-2" id="editForm<?= $n['id'] ?>">
                <div class="card card-body">
                  <form method="POST">
                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_news'] ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $n['id'] ?>">
                    <div class="mb-2">
                      <label class="form-label">Titolo</label>
                      <input type="text" name="titolo" class="form-control" maxlength="150" value="<?= htmlspecialchars($n['titolo']) ?>" required>
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Contenuto</label>
                      <textarea name="contenuto" class="form-control" rows="4" required><?= htmlspecialchars($n['contenuto']) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-success">Aggiorna</button>
                  </form>
                </div>
              </div>
            <?php endif; ?>

            <hr>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'navbar.php'; ?>
<?php include '../footer.php'; ?>
</body>
</html>

