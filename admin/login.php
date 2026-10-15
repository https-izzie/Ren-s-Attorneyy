<?php
require '../includes/auth.php';
require '../includes/db_staff.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, full_name, password_hash FROM staff WHERE email = ?');
    $stmt->execute([$email]);
    $staff = $stmt->fetch();

    if ($staff && password_verify($password, $staff['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['staff_id']   = $staff['id'];
        $_SESSION['staff_name'] = $staff['full_name'];
        header('Location: consultations.php');
        exit;
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Login | Rens Attorney</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
 <body>
  <div class="login-box">
    <h1>Rens Attorney<br><small style="font-size:13px;color:#a79b8a">Staff Login</small></h1>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Log in</button>
    </form>
  </div>
</body>
</body>
</html>