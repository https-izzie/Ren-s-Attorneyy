<?php
require '../includes/auth.php';
require_staff();
require '../includes/db_staff.php';

// Security token so only this page can change a status
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

// ---- Save a new status ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $okToken  = hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '');
    $statuses = ['new', 'contacted', 'scheduled', 'closed'];
    $status   = $_POST['status'] ?? '';
    $id       = (int)($_POST['id'] ?? 0);

    if ($okToken && $id && in_array($status, $statuses, true)) {
        $up = $pdo->prepare('UPDATE consultations SET status = ? WHERE id = ?');
        $up->execute([$status, $id]);
    }
    header('Location: consultations.php');
    exit;
}

// ---- Load requests and their files ----
$rows = $pdo->query('SELECT * FROM consultations ORDER BY created_at DESC')->fetchAll();

$filesByRequest = [];
foreach ($pdo->query('SELECT id, consultation_id, original_filename FROM consultation_files') as $f) {
    $filesByRequest[$f['consultation_id']][] = $f;
}

function e($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Consultations | Rens Attorney</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="top">
    <h1>Consultation Requests</h1>
    <div>Logged in as <?= e($_SESSION['staff_name']) ?> | <a href="logout.php">Log out</a></div>
  </div>

  <?php if (!$rows): ?>
    <p>No requests yet.</p>
  <?php else: ?>
  <div class="table-wrap"><table>
    <tr>
      <th>Date</th><th>Client</th><th>Contact</th><th>Matter</th>
      <th>Message</th><th>Files</th><th>Status</th>
    </tr>
    <?php foreach ($rows as $r): ?>
    <tr class="status-<?= e($r['status']) ?>">
      <td><?= e($r['created_at']) ?></td>
      <td><?= e($r['full_name']) ?><br><small><?= e($r['state']) ?></small></td>
      <td><?= e($r['email']) ?><br><?= e($r['phone']) ?></td>
      <td><?= e($r['practice_area']) ?><br><small><?= e($r['consult_type']) ?></small></td>
      <td class="msg"><?= e($r['message']) ?></td>
      <td>
        <?php foreach ($filesByRequest[$r['id']] ?? [] as $f): ?>
          <a href="download.php?id=<?= (int)$f['id'] ?>"><?= e($f['original_filename']) ?></a><br>
        <?php endforeach; ?>
      </td>
      <td>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <select name="status">
            <?php foreach (['new', 'contacted', 'scheduled', 'closed'] as $s): ?>
              <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit">Save</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table></div>
  <?php endif; ?>
</body>
</html>