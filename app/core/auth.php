<?php
class AuthController {
  public static function loginForm() { require __DIR__ . '/../../views/auth/login.php'; }

  public static function login() {
    csrf_verify();
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    $stmt = db()->prepare("SELECT * FROM users WHERE username=? AND is_active=1 LIMIT 1");
    $stmt->execute([$u]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($p, $user['password_hash'])) {
      flash('error','Credenziali non valide');
      redirect('/login');
    }

    $_SESSION['user'] = [
      'id' => (int)$user['id'],
      'username' => $user['username'],
      'name' => $user['first_name'].' '.$user['last_name'],
      'role' => $user['role'],
    ];
    redirect('/');
  }

  public static function logout() {
    csrf_verify();
    session_destroy();
    redirect('/login');
  }
}

function require_login(): bool {
  if (!isset($_SESSION['user'])) { redirect('/login'); return false; }
  return true;
}

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}
