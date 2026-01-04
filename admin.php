<?php
require 'config.php';
apply_security_headers();
require_admin();

// --- Handle CSV Export ---
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="political-ads-' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    $stmt = $pdo->query("SELECT * FROM political_ads ORDER BY id DESC");

    $header = [];
    for ($i = 0; $i < $stmt->columnCount(); $i++) {
        $col = $stmt->getColumnMeta($i);
        $header[] = $col['name'];
    }
    fputcsv($output, $header, ',', '"', '\\');

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row, ',', '"', '\\');
    }
    fclose($output);
    exit;
}

$message = null;

// Update record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    csrf_validate($_POST['csrf'] ?? '');

    $id = (int)$_POST['id'];

    $stmt = $pdo->prepare("
        UPDATE political_ads SET
            invoiced_amount = :invoiced_amount,
            currency = :currency,
            other_benefits = :other_benefits,
            calculation_method = :calculation_method,
            specific_service = :specific_service,
            previously_suspended = :previously_suspended,
            suspension_reason = :suspension_reason,
            eu_repository_link = :eu_repository_link,
            reach = :reach,
            targeting_personal_data = :targeting_personal_data,
            targeting_categories = :targeting_categories,
            targeting_data_sources = :targeting_data_sources,
            targeting_goals = :targeting_goals,
            targeting_parameters = :targeting_parameters,
            targeting_ai_use = :targeting_ai_use,
            targeting_policy_link = :targeting_policy_link,
            targeting_rights_link = :targeting_rights_link,
            status = :status,
            updated_at = datetime('now')
        WHERE id = :id
    ");

    $stmt->execute([
        ':invoiced_amount' => ($_POST['invoiced_amount'] !== '' ? (float)$_POST['invoiced_amount'] : null),
        ':currency' => trim((string)($_POST['currency'] ?? 'EUR')) ?: 'EUR',
        ':other_benefits' => trim((string)($_POST['other_benefits'] ?? '')),
        ':calculation_method' => trim((string)($_POST['calculation_method'] ?? '')),
        ':specific_service' => trim((string)($_POST['specific_service'] ?? '')),
        ':previously_suspended' => (($_POST['previously_suspended'] ?? 'No') === 'Yes' ? 'Yes' : 'No'),
        ':suspension_reason' => trim((string)($_POST['suspension_reason'] ?? '')),
        ':eu_repository_link' => trim((string)($_POST['eu_repository_link'] ?? '')),
        ':reach' => trim((string)($_POST['reach'] ?? '')),
        ':targeting_personal_data' => (($_POST['targeting_personal_data'] ?? 'No') === 'Yes' ? 'Yes' : 'No'),
        ':targeting_categories' => trim((string)($_POST['targeting_categories'] ?? '')),
        ':targeting_data_sources' => trim((string)($_POST['targeting_data_sources'] ?? '')),
        ':targeting_goals' => trim((string)($_POST['targeting_goals'] ?? '')),
        ':targeting_parameters' => trim((string)($_POST['targeting_parameters'] ?? '')),
        ':targeting_ai_use' => trim((string)($_POST['targeting_ai_use'] ?? '')),
        ':targeting_policy_link' => trim((string)($_POST['targeting_policy_link'] ?? '')),
        ':targeting_rights_link' => trim((string)($_POST['targeting_rights_link'] ?? '')),
        ':status' => trim((string)($_POST['status'] ?? 'draft')),
        ':id' => $id,
    ]);

    header('Location: admin.php?id=' . urlencode((string)$id) . '&saved=1');
    exit;
}

if (isset($_GET['saved']) && $_GET['saved'] === '1') {
    $message = 'Changes saved.';
}

$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$ad = null;

if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM political_ads WHERE id = :id");
    $stmt->execute([':id' => $editId]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmtList = $pdo->query("
    SELECT id, ad_title, sponsor_name, medium, start_date, end_date, status
    FROM political_ads
    ORDER BY start_date DESC, id DESC
");
$ads = $stmtList->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin – Political Ads</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { --ink:#1a1b1f; --muted:#5c5f6a; --accent:#df4d2a; --accent-dark:#b53b1f; --paper:#f7f2ed; --panel:#fff; --line:#e3d8cd; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:"Manrope","Noto Sans","Helvetica Neue",sans-serif; background:var(--paper); color:var(--ink); }
        .wrap { max-width: 1200px; margin: 0 auto; padding: 2rem 1.2rem 3rem; }
        header { padding-bottom:1rem; border-bottom:3px solid var(--accent); margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; flex-wrap:wrap; }
        header h1 { margin:0; font-size:1.7rem; font-family:"Spectral","Cormorant Garamond",serif; }
        header .actions { display:flex; gap:.6rem; flex-wrap:wrap; }
        a.btn { display:inline-block; padding:.35rem .8rem; border-radius:999px; background:var(--accent); color:#fff; text-decoration:none; font-size:.9rem; }
        a.btn:hover { background:var(--accent-dark); }
        a.link { color:var(--accent-dark); text-decoration:none; font-size:.9rem; }
        a.link:hover { text-decoration:underline; }
        .message { background:#e8f8ec; border:1px solid #9fd3aa; padding:.6rem .8rem; border-radius:4px; margin-bottom:1rem; }
        .layout { display:grid; grid-template-columns: minmax(0,2fr) minmax(0,3fr); gap:1rem; }
        table { width:100%; border-collapse:collapse; background:var(--panel); border-radius:10px; overflow:hidden; font-size:.9rem; }
        th, td { border:1px solid var(--line); padding:.45rem .6rem; text-align:left; vertical-align:top; }
        th { background:#f0e6dc; font-weight:600; }
        tbody tr:nth-child(even) { background:#faf6f2; }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:10px; padding:1rem 1.2rem; }
        h2 { margin-top:0; font-size:1.1rem; }
        label { display:block; margin-top:.45rem; font-size:.9rem; }
        input[type="text"], textarea, select { width:100%; padding:.4rem .55rem; border:1px solid var(--line); border-radius:4px; font:inherit; margin-top:.2rem; }
        textarea { resize:vertical; }
        button { background:var(--accent); border:0; color:#fff; padding:.55rem 1.2rem; border-radius:999px; cursor:pointer; margin-top:.8rem; }
        button:hover { background:var(--accent-dark); }
        .small { font-size:.85rem; color:var(--muted); margin-top:.6rem; }
        .pill { display:inline-block; padding:.1rem .45rem; border-radius:999px; font-size:.8rem; border:1px solid #e1c7b7; background:#fff6f0; }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <div>
            <h1>Admin – Political Ads</h1>
            <div class="small">Complete internal fields and publish transparency notices.</div>
        </div>
        <div class="actions">
            <a class="btn" href="admin.php?export=csv">Export CSV</a>
            <a class="link" href="register.php">Open public register</a>
            <a class="link" href="logout.php">Sign out</a>
        </div>
    </header>

    <?php if ($message): ?>
        <div class="message"><?php echo h($message); ?></div>
    <?php endif; ?>

    <div class="layout">
        <div>
            <h2>All ads</h2>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Ad</th>
                    <th>Sponsor</th>
                    <th>Medium</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th>Edit</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($ads as $a): ?>
                    <tr>
                        <td><?php echo h((string)$a['id']); ?></td>
                        <td><?php echo h((string)$a['ad_title']); ?></td>
                        <td><?php echo h((string)$a['sponsor_name']); ?></td>
                        <td><?php echo h((string)$a['medium']); ?></td>
                        <td><?php echo h((string)$a['start_date']); ?> – <?php echo h((string)$a['end_date']); ?></td>
                        <td><span class="pill"><?php echo h((string)($a['status'] ?: 'unknown')); ?></span></td>
                        <td><a class="btn" href="admin.php?id=<?php echo h((string)$a['id']); ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div>
            <div class="card">
                <h2>Edit ad</h2>
                <?php if (!$ad): ?>
                    <p class="small">Select an ad from the list.</p>
                <?php else: ?>
                    <p><strong>#<?php echo h((string)$ad['id']); ?></strong> – <?php echo h((string)$ad['ad_title']); ?></p>
                    <p class="small">
                        Sponsor: <?php echo h((string)$ad['sponsor_name']); ?><br>
                        Period: <?php echo h((string)$ad['start_date']); ?> – <?php echo h((string)$ad['end_date']); ?><br>
                        Transparency: <a class="link" href="transparency.php?id=<?php echo h((string)$ad['id']); ?>">Open</a>
                    </p>

                    <form method="post">
                        <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
                        <input type="hidden" name="id" value="<?php echo h((string)$ad['id']); ?>">

                        <label>Invoiced amount
                            <input type="text" name="invoiced_amount" value="<?php echo h((string)$ad['invoiced_amount']); ?>">
                        </label>

                        <label>Currency
                            <input type="text" name="currency" value="<?php echo h((string)($ad['currency'] ?: 'EUR')); ?>">
                        </label>

                        <label>Other benefits (discounts, packages, barters)
                            <textarea name="other_benefits" rows="2"><?php echo h((string)$ad['other_benefits']); ?></textarea>
                        </label>

                        <label>Calculation method
                            <textarea name="calculation_method" rows="2"><?php echo h((string)$ad['calculation_method']); ?></textarea>
                        </label>

                        <label>Specific service details
                            <textarea name="specific_service" rows="2"><?php echo h((string)$ad['specific_service']); ?></textarea>
                        </label>

                        <label>Targeting uses personal data?
                            <select name="targeting_personal_data">
                                <option value="No" <?php if (($ad['targeting_personal_data'] ?? 'No') !== 'Yes') echo 'selected'; ?>>No</option>
                                <option value="Yes" <?php if (($ad['targeting_personal_data'] ?? '') === 'Yes') echo 'selected'; ?>>Yes</option>
                            </select>
                        </label>

                        <label>Targeting parameters
                            <textarea name="targeting_parameters" rows="2"><?php echo h((string)$ad['targeting_parameters']); ?></textarea>
                        </label>

                        <label>Targeting goals
                            <textarea name="targeting_goals" rows="2"><?php echo h((string)$ad['targeting_goals']); ?></textarea>
                        </label>

                        <label>Personal data categories
                            <textarea name="targeting_categories" rows="2"><?php echo h((string)$ad['targeting_categories']); ?></textarea>
                        </label>

                        <label>Data sources
                            <textarea name="targeting_data_sources" rows="2"><?php echo h((string)$ad['targeting_data_sources']); ?></textarea>
                        </label>

                        <label>AI used
                            <input type="text" name="targeting_ai_use" value="<?php echo h((string)$ad['targeting_ai_use']); ?>">
                        </label>

                        <label>Targeting policy link
                            <input type="text" name="targeting_policy_link" value="<?php echo h((string)$ad['targeting_policy_link']); ?>">
                        </label>

                        <label>Rights/consent management link
                            <input type="text" name="targeting_rights_link" value="<?php echo h((string)$ad['targeting_rights_link']); ?>">
                        </label>

                        <label>Has the ad been suspended due to infringement?
                            <select name="previously_suspended">
                                <option value="No" <?php if (($ad['previously_suspended'] ?? 'No') !== 'Yes') echo 'selected'; ?>>No</option>
                                <option value="Yes" <?php if (($ad['previously_suspended'] ?? '') === 'Yes') echo 'selected'; ?>>Yes</option>
                            </select>
                        </label>

                        <label>If yes, reason
                            <textarea name="suspension_reason" rows="2"><?php echo h((string)$ad['suspension_reason']); ?></textarea>
                        </label>

                        <label>EU repository link (online ads)
                            <input type="text" name="eu_repository_link" value="<?php echo h((string)$ad['eu_repository_link']); ?>">
                        </label>

                        <label>Reach / outcomes
                            <textarea name="reach" rows="2"><?php echo h((string)$ad['reach']); ?></textarea>
                        </label>

                        <label>Status
                            <select name="status">
                                <?php
                                $statuses = ['draft' => 'Draft', 'scheduled' => 'Scheduled', 'published' => 'Published', 'ended' => 'Ended'];
                                foreach ($statuses as $val => $label) {
                                    $sel = (($ad['status'] ?? 'draft') === $val) ? 'selected' : '';
                                    echo '<option value="' . h($val) . '" ' . $sel . '>' . h($label) . '</option>';
                                }
                                ?>
                            </select>
                        </label>

                        <button type="submit">Save changes</button>
                        <div class="small">Last updated: <?php echo h((string)($ad['updated_at'] ?: $ad['created_at'])); ?></div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
