<?php
require 'config.php';
apply_security_headers();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM political_ads WHERE id = :id");
$stmt->execute([':id' => $id]);
$ad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ad) {
    http_response_code(404);
    exit('No advertisement found.');
}

if (($ad['status'] ?? 'draft') === 'draft' && !is_admin()) {
    http_response_code(404);
    exit('No advertisement found.');
}

$amount = $ad['invoiced_amount'];
if ($amount === null || $amount === '') {
    $amount = $ad['estimated_amount'];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Transparency Notice – <?php echo h((string)$ad['ad_title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { --ink:#1a1b1f; --muted:#5c5f6a; --accent:#df4d2a; --accent-dark:#b53b1f; --paper:#f7f2ed; --panel:#fff; --line:#e3d8cd; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:"Manrope","Noto Sans","Helvetica Neue",sans-serif; background:var(--paper); color:var(--ink); }
        .wrap { max-width: 1050px; margin:0 auto; padding: 2rem 1.2rem 3rem; }
        header { padding-bottom:1rem; border-bottom:3px solid var(--accent); margin-bottom:1.5rem; }
        header h1 { margin:0; font-size:1.9rem; font-family:"Spectral","Cormorant Garamond",serif; }
        header p { margin:.3rem 0 0; color:var(--muted); }
        .badge { display:inline-block; padding:.15rem .5rem; border-radius:4px; font-size:.8rem; background:#fff2eb; border:1px solid #ffd0c2; color:#9f3a23; margin-left:.4rem; }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:10px; padding:1.1rem 1.3rem; margin-bottom:1rem; }
        h2 { margin-top:0; font-size:1.1rem; }
        dl { margin:.2rem 0; }
        dt { font-weight:600; margin-top:.5rem; }
        dd { margin:.1rem 0 .4rem 0; color:var(--muted); }
        a { color:var(--accent-dark); }
        .note { font-size:.85rem; color:var(--muted); margin-top:.7rem; }
        .pill { display:inline-block; font-size:.8rem; padding:.1rem .5rem; border-radius:999px; border:1px solid #e1c7b7; background:#fff6f0; }
        .back { font-size:.9rem; margin-bottom:1rem; }
        .back a { text-decoration:none; }
        .back a:hover { text-decoration:underline; }
        .footer { margin-top:1.5rem; font-size:.85rem; color:var(--muted); }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <h1>Transparency Notice<span class="badge">Political advertisement</span></h1>
        <p>Information required under Regulation (EU) 2024/900 (TTPA).</p>
    </header>

    <div class="back"><a href="register.php">&larr; Back to public register</a></div>

    <div class="card">
        <h2>Core details</h2>
        <dl>
            <dt>Ad ID</dt>
            <dd><?php echo h((string)$ad['id']); ?></dd>

            <dt>Status</dt>
            <dd><span class="pill"><?php echo h((string)($ad['status'] ?: 'unknown')); ?></span></dd>

            <dt>Short title</dt>
            <dd><?php echo h((string)$ad['ad_title']); ?></dd>

            <dt>Description</dt>
            <dd><?php echo nl2br(h((string)$ad['ad_description'])); ?></dd>

            <dt>Medium</dt>
            <dd><?php echo h((string)($ad['medium'] ?: 'Not specified')); ?></dd>

            <dt>Publication period</dt>
            <dd><?php echo h((string)$ad['start_date']); ?> – <?php echo h((string)$ad['end_date']); ?></dd>
        </dl>
    </div>

    <div class="card">
        <h2>Sponsor and payer</h2>
        <dl>
            <dt>Sponsor</dt>
            <dd>
                <?php echo h((string)$ad['sponsor_name']); ?><br>
                <?php if (!empty($ad['sponsor_entity_type'])) echo h((string)$ad['sponsor_entity_type']) . "<br>"; ?>
                <?php if (!empty($ad['sponsor_controller'])) echo "Ultimate controller: " . h((string)$ad['sponsor_controller']) . "<br>"; ?>
                <?php if (!empty($ad['sponsor_address'])) echo h((string)$ad['sponsor_address']) . "<br>"; ?>
                Email: <?php echo h((string)$ad['sponsor_email']); ?><br>
                <?php if (!empty($ad['sponsor_country'])) echo "Country: " . h((string)$ad['sponsor_country']); ?>
            </dd>

            <dt>Payer (if different from sponsor)</dt>
            <dd>
                <?php if (!empty($ad['payer_name'])): ?>
                    <?php echo h((string)$ad['payer_name']); ?><br>
                    <?php if (!empty($ad['payer_address'])) echo h((string)$ad['payer_address']) . "<br>"; ?>
                    <?php if (!empty($ad['payer_email'])) echo "Email: " . h((string)$ad['payer_email']) . "<br>"; ?>
                    <?php if (!empty($ad['payer_country'])) echo "Country: " . h((string)$ad['payer_country']); ?>
                <?php else: ?>
                    The sponsor is also the payer.
                <?php endif; ?>
            </dd>
        </dl>
    </div>

    <div class="card">
        <h2>Amounts and funding</h2>
        <dl>
            <dt>Amounts and other benefits</dt>
            <dd>
                <?php if ($amount !== null && $amount !== ''): ?>
                    Amount: <?php echo h((string)$amount); ?> <?php echo h((string)($ad['currency'] ?: 'EUR')); ?><br>
                <?php else: ?>
                    Amount will be updated after invoicing.<br>
                <?php endif; ?>

                <?php if (!empty($ad['other_benefits'])): ?>
                    Other benefits: <?php echo nl2br(h((string)$ad['other_benefits'])); ?>
                <?php endif; ?>
            </dd>

            <dt>Calculation method</dt>
            <dd><?php echo !empty($ad['calculation_method']) ? nl2br(h((string)$ad['calculation_method'])) : "Rate card and applicable discounts or agreements."; ?></dd>

            <dt>Funding sources</dt>
            <dd>
                <?php echo h((string)($ad['funding_sources'] ?: 'Not specified')); ?><br>
                Origin: <?php echo h((string)($ad['funding_origin_eu'] ?: 'Not specified')); ?>
            </dd>
        </dl>
    </div>

    <div class="card">
        <h2>Link to election or political process</h2>
        <dl>
            <dt>Link type</dt>
            <dd><?php echo h((string)($ad['link_type'] ?: 'General political debate')); ?></dd>

            <dt>Description</dt>
            <dd><?php echo nl2br(h((string)$ad['link_description'])); ?></dd>

            <dt>Official information link (elections/referendums)</dt>
            <dd>
                <?php if (!empty($ad['official_info_link'])): ?>
                    <a href="<?php echo h((string)$ad['official_info_link']); ?>" target="_blank" rel="noopener noreferrer"><?php echo h((string)$ad['official_info_link']); ?></a>
                <?php else: ?>
                    Not applicable or not provided.
                <?php endif; ?>
            </dd>
        </dl>
    </div>

    <div class="card">
        <h2>Targeting and data use</h2>
        <dl>
            <dt>Personal data used for targeting/ad delivery</dt>
            <dd>
                <?php echo h((string)($ad['targeting_personal_data'] ?: 'No')); ?>
                <?php if (($ad['targeting_personal_data'] ?? 'No') === 'Yes'): ?>
                    <br><strong>Targeted groups/parameters:</strong><br><?php echo nl2br(h((string)$ad['targeting_parameters'])); ?>
                    <br><br><strong>Targeting goals:</strong><br><?php echo nl2br(h((string)$ad['targeting_goals'])); ?>
                    <br><br><strong>Personal data categories:</strong><br><?php echo nl2br(h((string)$ad['targeting_categories'])); ?>
                    <br><br><strong>Data sources:</strong><br><?php echo nl2br(h((string)$ad['targeting_data_sources'])); ?>
                    <br><br><strong>AI used:</strong><br><?php echo nl2br(h((string)($ad['targeting_ai_use'] ?: 'No'))); ?>
                <?php endif; ?>
            </dd>

            <dt>Targeting policy</dt>
            <dd>
                <?php if (!empty($ad['targeting_policy_link'])): ?>
                    <a href="<?php echo h((string)$ad['targeting_policy_link']); ?>" target="_blank" rel="noopener noreferrer"><?php echo h((string)$ad['targeting_policy_link']); ?></a>
                <?php else: ?>
                    Not provided.
                <?php endif; ?>
            </dd>

            <dt>Rights and consent management</dt>
            <dd>
                <?php if (!empty($ad['targeting_rights_link'])): ?>
                    <a href="<?php echo h((string)$ad['targeting_rights_link']); ?>" target="_blank" rel="noopener noreferrer"><?php echo h((string)$ad['targeting_rights_link']); ?></a>
                <?php else: ?>
                    Not provided.
                <?php endif; ?>
            </dd>
        </dl>
    </div>

    <div class="card">
        <h2>Additional information</h2>
        <dl>
            <dt>EU repository link (online ads)</dt>
            <dd>
                <?php if (!empty($ad['eu_repository_link'])): ?>
                    <a href="<?php echo h((string)$ad['eu_repository_link']); ?>" target="_blank" rel="noopener noreferrer"><?php echo h((string)$ad['eu_repository_link']); ?></a>
                <?php else: ?>
                    Not applicable or pending.
                <?php endif; ?>
            </dd>

            <dt>Complaints or questions</dt>
            <dd>Contact the editorial team: <a href="mailto:<?php echo h(EDITORIAL_EMAIL); ?>"><?php echo h(EDITORIAL_EMAIL); ?></a></dd>

            <dt>Has this ad been suspended due to an infringement?</dt>
            <dd>
                <?php if (($ad['previously_suspended'] ?? 'No') === 'Yes'): ?>
                    Yes – <?php echo nl2br(h((string)$ad['suspension_reason'])); ?>
                <?php else: ?>
                    No.
                <?php endif; ?>
            </dd>

            <dt>Reach / outcomes</dt>
            <dd><?php echo h((string)($ad['reach'] ?: 'Will be updated after the campaign.')); ?></dd>
        </dl>

        <p class="note">This notice is retained for at least seven years after publication.</p>
    </div>

    <div class="footer">
        Last updated: <?php echo h((string)($ad['updated_at'] ?: $ad['created_at'])); ?>
    </div>
</div>
</body>
</html>
