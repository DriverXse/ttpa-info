<?php
require 'config.php';
apply_security_headers();

if (is_admin_configured($pdo)) {
    header('Location: login.php');
    exit;
}

$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_POST['csrf'] ?? '');

    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    if (strlen($password) < 12) {
        $error = 'Password must be at least 12 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        set_admin_password($pdo, $password);
        $success = true;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Setup</title>
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
        .setup-box {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 1.8rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 14px 40px rgba(27, 22, 18, 0.14);
        }
        h1 { margin: 0 0 .6rem 0; font-size: 1.4rem; font-family: "Spectral", "Cormorant Garamond", serif; }
        label { display: block; margin-top: .6rem; font-size: .95rem; }
        input { width: 100%; padding: .45rem .55rem; border: 1px solid var(--line); border-radius: 4px; margin-top: .25rem; font: inherit; }
        button { width: 100%; margin-top: 1rem; padding: .6rem .8rem; border: 0; border-radius: 999px; background: var(--accent); color: #fff; font-size: 1rem; cursor: pointer; }
        button:hover { background: var(--accent-dark); }
        .error { background: #ffecec; border: 1px solid #f5a1a1; color: #8a1f1f; padding: .6rem .8rem; border-radius: 4px; margin-top: .8rem; }
        .success { background: #e8f8ec; border: 1px solid #9fd3aa; color: #21552e; padding: .6rem .8rem; border-radius: 4px; margin-top: .8rem; }
        .small { font-size: .85rem; color: var(--muted); margin-top: .8rem; }
    </style>
</head>
<body>
<div class="setup-box">
    <h1>Set admin password</h1>
    <?php if ($success): ?>
        <div class="success">
            Password set. You can now <a href="login.php">sign in</a> with username <strong>admin</strong>.
        </div>
    <?php else: ?>
        <form method="post" autocomplete="off">
            <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
            <label>Password (min 12 characters)
                <input type="password" name="password" required>
            </label>
            <label>Confirm password
                <input type="password" name="confirm_password" required>
            </label>
            <button type="submit">Save password</button>
            <?php if ($error): ?>
                <div class="error"><?php echo h($error); ?></div>
            <?php endif; ?>
        </form>
        <div class="small">This setup screen is available only until an admin password is set.</div>
    <?php endif; ?>
</div>
</body>
</html>
