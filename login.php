<?php
require 'config.php';
apply_security_headers();

$error = null;

if (!is_admin_configured($pdo)) {
    header('Location: setup.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_POST['csrf'] ?? '');

    $user = trim((string)($_POST['username'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');

    $hash = admin_password_hash($pdo);
    if ($user === ADMIN_USERNAME && $hash && password_verify($pass, $hash)) {
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            set_admin_password($pdo, $pass);
        }
        session_regenerate_id(true);
        $_SESSION['is_admin_logged_in'] = true;

        header('Location: admin.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { --ink:#1a1b1f; --muted:#5c5f6a; --accent:#df4d2a; --accent-dark:#b53b1f; --paper:#f7f2ed; --panel:#fff; --line:#e3d8cd; }
        body {
            font-family: "Manrope", "Noto Sans", "Helvetica Neue", sans-serif;
            background: var(--paper);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 1.6rem;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 14px 40px rgba(27, 22, 18, 0.14);
        }
        h1 { margin: 0 0 .6rem 0; font-size: 1.4rem; font-family: "Spectral", "Cormorant Garamond", serif; }
        label { display: block; margin-top: .6rem; font-size: .95rem; }
        input { width: 100%; padding: .45rem .55rem; border: 1px solid var(--line); border-radius: 4px; margin-top: .25rem; font: inherit; }
        button { width: 100%; margin-top: 1rem; padding: .6rem .8rem; border: 0; border-radius: 999px; background: var(--accent); color: #fff; font-size: 1rem; cursor: pointer; }
        button:hover { background: var(--accent-dark); }
        .error { background: #ffecec; border: 1px solid #f5a1a1; color: #8a1f1f; padding: .6rem .8rem; border-radius: 4px; margin-top: .8rem; }
        .small { font-size: .85rem; color: var(--muted); margin-top: .8rem; }
    </style>
</head>
<body>
<div class="login-box">
    <h1>Admin</h1>
    <form method="post" autocomplete="off">
        <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
        <label>Username
            <input type="text" name="username" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit">Sign in</button>
        <?php if ($error): ?>
            <div class="error"><?php echo h($error); ?></div>
        <?php endif; ?>
    </form>
    <div class="small">Restricted editorial access.</div>
</div>
</body>
</html>
