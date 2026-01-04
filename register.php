<?php
require 'config.php';
apply_security_headers();

$stmt = $pdo->query("
    SELECT id, ad_title, sponsor_name, medium, start_date, end_date, status
    FROM political_ads
    WHERE status IN ('scheduled','published','ended')
    ORDER BY start_date DESC, id DESC
");
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Public Register of Political Ads</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { --ink:#1a1b1f; --muted:#5c5f6a; --accent:#df4d2a; --accent-dark:#b53b1f; --paper:#f7f2ed; --panel:#fff; --line:#e3d8cd; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:"Manrope","Noto Sans","Helvetica Neue",sans-serif; background:var(--paper); color:var(--ink); }
        .wrap { max-width: 1100px; margin: 0 auto; padding: 2rem 1.2rem 3rem; }
        header { display:flex; flex-wrap:wrap; justify-content:space-between; gap:1rem; align-items:flex-end; margin-bottom:1.4rem; }
        h1 { margin:0; font-size:1.9rem; font-family:"Spectral","Cormorant Garamond",serif; }
        .sub { color:var(--muted); margin:.3rem 0 0; }
        .actions a { text-decoration:none; color:var(--accent-dark); font-weight:600; }
        .note { font-size:.95rem; color:var(--muted); margin-bottom:1rem; }
        table { width:100%; border-collapse:collapse; background:var(--panel); border-radius:10px; overflow:hidden; font-size:.92rem; }
        th, td { border:1px solid var(--line); padding:.55rem .7rem; text-align:left; }
        th { background:#f0e6dc; font-weight:600; }
        tbody tr:nth-child(even) { background:#faf6f2; }
        a { color:var(--accent-dark); }
        .pill { display:inline-block; padding:.1rem .55rem; border-radius:999px; font-size:.8rem; border:1px solid #e1c7b7; background:#fff6f0; }
        .footer { margin-top:1.4rem; font-size:.9rem; color:var(--muted); }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <div>
            <h1>Public Register of Political Ads</h1>
            <div class="sub">Transparency information for ads published or scheduled under the TTPA rules.</div>
        </div>
        <div class="actions">
            <a href="index.php">Home</a>
        </div>
    </header>

    <p class="note">
        Each listing links to a transparency notice with sponsor, funding, and targeting details. Records are retained for
        at least seven years.
    </p>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Ad</th>
            <th>Sponsor</th>
            <th>Medium</th>
            <th>Period</th>
            <th>Status</th>
            <th>Transparency</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$ads): ?>
            <tr><td colspan="7">No political ads registered yet.</td></tr>
        <?php else: ?>
            <?php foreach ($ads as $a): ?>
                <tr>
                    <td><?php echo h((string)$a['id']); ?></td>
                    <td><?php echo h((string)$a['ad_title']); ?></td>
                    <td><?php echo h((string)$a['sponsor_name']); ?></td>
                    <td><?php echo h((string)$a['medium']); ?></td>
                    <td><?php echo h((string)$a['start_date']); ?> – <?php echo h((string)$a['end_date']); ?></td>
                    <td><span class="pill"><?php echo h((string)$a['status']); ?></span></td>
                    <td><a href="transparency.php?id=<?php echo h((string)$a['id']); ?>">View notice</a></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Contact: <a href="mailto:<?php echo h(EDITORIAL_EMAIL); ?>"><?php echo h(EDITORIAL_EMAIL); ?></a>
        <?php if (is_admin()): ?>
            — <a href="admin.php">Admin</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
