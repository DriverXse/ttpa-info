<?php
require 'config.php';
apply_security_headers();

$errors = [];
$success = false;
$id = null;

// simple per-session rate limit (prevents accidental double submits)
if (!isset($_SESSION['last_submit_ts'])) {
    $_SESSION['last_submit_ts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_POST['csrf'] ?? '');

    // Honeypot (bots)
    if (!empty($_POST['website'])) {
        http_response_code(200);
        exit;
    }

    $now = time();
    if ($now - (int)$_SESSION['last_submit_ts'] < 3) {
        $errors[] = 'Please wait a few seconds and try again.';
    }

    $sponsor_name = trim((string)($_POST['sponsor_name'] ?? ''));
    $sponsor_email = trim((string)($_POST['sponsor_email'] ?? ''));
    $ad_title = trim((string)($_POST['ad_title'] ?? ''));
    $start_date = (string)($_POST['start_date'] ?? '');
    $end_date = (string)($_POST['end_date'] ?? '');

    if ($sponsor_name === '') $errors[] = 'Sponsor name is required.';
    if ($sponsor_email === '' || !filter_var($sponsor_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($ad_title === '') $errors[] = 'A short ad title is required.';
    if ($start_date === '' || $end_date === '') $errors[] = 'Start and end dates are required.';
    if ($start_date !== '' && $end_date !== '' && $end_date < $start_date) $errors[] = 'End date cannot be before start date.';

    if (!isset($_POST['certified_ttpa'])) $errors[] = 'You must confirm the TTPA compliance statement.';

    if (empty($errors)) {
        $sql = "
            INSERT INTO political_ads (
                sponsor_name, sponsor_entity_type, sponsor_registration_id,
                sponsor_email, sponsor_phone, sponsor_address, sponsor_country, sponsor_controller,
                payer_name, payer_type, payer_email, payer_address, payer_country,
                link_type, link_description, official_info_link,
                ad_title, ad_description, medium,
                start_date, end_date,
                estimated_amount, funding_type, funding_sources, funding_origin_eu,
                targeting_personal_data, targeting_categories, targeting_data_sources, targeting_goals,
                targeting_parameters, targeting_ai_use, targeting_policy_link, targeting_rights_link,
                certified_ttpa,
                invoiced_amount, currency, other_benefits, calculation_method,
                specific_service, previously_suspended, suspension_reason,
                eu_repository_link, reach,
                status, created_at, updated_at
            ) VALUES (
                :sponsor_name, :sponsor_entity_type, :sponsor_registration_id,
                :sponsor_email, :sponsor_phone, :sponsor_address, :sponsor_country, :sponsor_controller,
                :payer_name, :payer_type, :payer_email, :payer_address, :payer_country,
                :link_type, :link_description, :official_info_link,
                :ad_title, :ad_description, :medium,
                :start_date, :end_date,
                :estimated_amount, :funding_type, :funding_sources, :funding_origin_eu,
                :targeting_personal_data, :targeting_categories, :targeting_data_sources, :targeting_goals,
                :targeting_parameters, :targeting_ai_use, :targeting_policy_link, :targeting_rights_link,
                :certified_ttpa,
                NULL, NULL, NULL, NULL,
                NULL, 'No', NULL,
                NULL, NULL,
                'draft', datetime('now'), datetime('now')
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':sponsor_name' => $sponsor_name,
            ':sponsor_entity_type' => trim((string)($_POST['sponsor_entity_type'] ?? '')),
            ':sponsor_registration_id' => trim((string)($_POST['sponsor_registration_id'] ?? '')),
            ':sponsor_email' => $sponsor_email,
            ':sponsor_phone' => trim((string)($_POST['sponsor_phone'] ?? '')),
            ':sponsor_address' => trim((string)($_POST['sponsor_address'] ?? '')),
            ':sponsor_country' => trim((string)($_POST['sponsor_country'] ?? '')),
            ':sponsor_controller' => trim((string)($_POST['sponsor_controller'] ?? '')),
            ':payer_name' => trim((string)($_POST['payer_name'] ?? '')),
            ':payer_type' => trim((string)($_POST['payer_type'] ?? '')),
            ':payer_email' => trim((string)($_POST['payer_email'] ?? '')),
            ':payer_address' => trim((string)($_POST['payer_address'] ?? '')),
            ':payer_country' => trim((string)($_POST['payer_country'] ?? '')),
            ':link_type' => trim((string)($_POST['link_type'] ?? '')),
            ':link_description' => trim((string)($_POST['link_description'] ?? '')),
            ':official_info_link' => trim((string)($_POST['official_info_link'] ?? '')),
            ':ad_title' => $ad_title,
            ':ad_description' => trim((string)($_POST['ad_description'] ?? '')),
            ':medium' => trim((string)($_POST['medium'] ?? '')),
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':estimated_amount' => ($_POST['estimated_amount'] !== '' ? (float)$_POST['estimated_amount'] : null),
            ':funding_type' => trim((string)($_POST['funding_type'] ?? '')),
            ':funding_sources' => isset($_POST['funding_sources']) ? implode(',', (array)$_POST['funding_sources']) : '',
            ':funding_origin_eu' => trim((string)($_POST['funding_origin_eu'] ?? '')),
            ':targeting_personal_data' => (($_POST['targeting_personal_data'] ?? 'No') === 'Yes' ? 'Yes' : 'No'),
            ':targeting_categories' => trim((string)($_POST['targeting_categories'] ?? '')),
            ':targeting_data_sources' => trim((string)($_POST['targeting_data_sources'] ?? '')),
            ':targeting_goals' => trim((string)($_POST['targeting_goals'] ?? '')),
            ':targeting_parameters' => trim((string)($_POST['targeting_parameters'] ?? '')),
            ':targeting_ai_use' => trim((string)($_POST['targeting_ai_use'] ?? '')),
            ':targeting_policy_link' => trim((string)($_POST['targeting_policy_link'] ?? '')),
            ':targeting_rights_link' => trim((string)($_POST['targeting_rights_link'] ?? '')),
            ':certified_ttpa' => 'Yes',
        ]);

        $_SESSION['last_submit_ts'] = $now;

        $id = (int)$pdo->lastInsertId();
        $success = true;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Political Ad Submission – TTPA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { --ink:#1a1b1f; --muted:#5c5f6a; --accent:#df4d2a; --accent-dark:#b53b1f; --paper:#f7f2ed; --panel:#fff; --line:#e3d8cd; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:"Manrope","Noto Sans","Helvetica Neue",sans-serif; background:var(--paper); color:var(--ink); }
        .wrap { max-width: 1120px; margin: 0 auto; padding: 2rem 1.2rem 3rem; }
        header { padding-bottom:1rem; border-bottom:3px solid var(--accent); margin-bottom:1.5rem; }
        header h1 { margin:0; font-size:1.9rem; font-family:"Spectral","Cormorant Garamond",serif; }
        header p { margin:.3rem 0 0; color:var(--muted); }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:10px; padding:1rem 1.2rem; margin-bottom:1rem; }
        legend { font-weight:600; margin-bottom:.5rem; padding:0; }
        label { display:block; margin-top:.45rem; font-size:.95rem; }
        input[type="text"], input[type="email"], input[type="date"], textarea, select {
            width:100%; padding:.45rem .55rem; margin-top:.2rem; border-radius:4px; border:1px solid var(--line); font:inherit;
        }
        textarea { resize: vertical; }
        .inline-checkbox { display:flex; gap:1rem; flex-wrap:wrap; margin-top:.4rem; }
        .inline-checkbox label { display:flex; align-items:center; gap:.3rem; margin-top:0; }
        .error { background:#ffecec; border:1px solid #f5a1a1; color:#8a1f1f; padding:.7rem 1rem; margin-bottom:1rem; border-radius:4px; }
        .success { background:#e8f8ec; border:1px solid #9fd3aa; color:#21552e; padding:.7rem 1rem; margin-bottom:1rem; border-radius:4px; }
        button[type="submit"] { background:var(--accent); border:0; color:#fff; padding:.6rem 1.5rem; border-radius:999px; font-size:1rem; cursor:pointer; margin-top:.5rem; }
        button[type="submit"]:hover { background:var(--accent-dark); }
        .small { font-size:.85rem; color:var(--muted); }
        .two-col { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:.8rem 1.2rem; }
        .top-helper { font-size:.92rem; background:#fff2eb; border:1px solid #ffd0c2; border-radius:6px; padding:.7rem .9rem; margin-bottom:1rem; }
        code { font-size:.85rem; background:#f1e8e1; padding:.1rem .3rem; border-radius:3px; }
        .honeypot { position:absolute; left:-10000px; top:auto; width:1px; height:1px; overflow:hidden; }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <h1>Political Ad Submission (TTPA)</h1>
        <p>Provide the information required for a transparency notice under Regulation (EU) 2024/900.</p>
    </header>

    <?php if ($success && $id): ?>
        <div class="success">
            Thank you! Your submission has been recorded.<br>
            Your ad ID: <strong><?php echo h((string)$id); ?></strong><br>
            Transparency notice will be available at:<br>
            <code>transparency.php?id=<?php echo h((string)$id); ?></code>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="error">
            <strong>Please fix the following:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo h($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="top-helper">
        Complete all sections as accurately as possible. The information will be used for public labeling and the
        transparency notice. You may be contacted to complete or verify the details.
    </div>

    <form method="post">
        <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
        <div class="honeypot">
            <label>Website <input type="text" name="website" value=""></label>
        </div>

        <div class="card">
            <fieldset>
                <legend>1. Sponsor (responsible for the message)</legend>
                <div class="two-col">
                    <div>
                        <label>Sponsor name*<input type="text" name="sponsor_name" required></label>
                        <label>Entity type (party, NGO, company, individual...)
                            <input type="text" name="sponsor_entity_type">
                        </label>
                        <label>Registration ID / VAT / national ID
                            <input type="text" name="sponsor_registration_id">
                        </label>
                        <label>Ultimate controlling entity (if applicable)
                            <input type="text" name="sponsor_controller">
                        </label>
                    </div>
                    <div>
                        <label>Email*<input type="email" name="sponsor_email" required></label>
                        <label>Phone<input type="text" name="sponsor_phone"></label>
                        <label>Address<input type="text" name="sponsor_address"></label>
                        <label>Country<input type="text" name="sponsor_country" value=""></label>
                    </div>
                </div>
            </fieldset>
        </div>

        <div class="card">
            <fieldset>
                <legend>2. Payer (if different from sponsor)</legend>
                <div class="two-col">
                    <div>
                        <label>Payer name
                            <input type="text" name="payer_name">
                        </label>
                        <label>Type
                            <select name="payer_type">
                                <option value="">Select...</option>
                                <option value="individual">Individual</option>
                                <option value="organization">Organization</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label>Payer email
                            <input type="email" name="payer_email">
                        </label>
                        <label>Payer address
                            <input type="text" name="payer_address">
                        </label>
                        <label>Payer country
                            <input type="text" name="payer_country">
                        </label>
                    </div>
                </div>
            </fieldset>
        </div>

        <div class="card">
            <fieldset>
                <legend>3. Link to election or political process</legend>
                <label>Link type
                    <select name="link_type">
                        <option value="">General political debate</option>
                        <option value="election">Election</option>
                        <option value="referendum">Referendum</option>
                        <option value="legislative">Legislative or regulatory process</option>
                    </select>
                </label>
                <label>Describe the relevant election, question, or process
                    <textarea name="link_description" rows="3"></textarea>
                </label>
                <label>Official information link (e.g. electoral authority)
                    <input type="text" name="official_info_link" placeholder="https://...">
                </label>
            </fieldset>
        </div>

        <div class="card">
            <fieldset>
                <legend>4. The advertisement</legend>
                <label>Short ad title*<input type="text" name="ad_title" required></label>
                <label>Message description (for identification)
                    <textarea name="ad_description" rows="3"></textarea>
                </label>
                <div class="two-col">
                    <div>
                        <label>Medium
                            <select name="medium">
                                <option value="print">Print</option>
                                <option value="web">Web</option>
                                <option value="print+web">Print + web</option>
                                <option value="other">Other</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label>Planned start date*<input type="date" name="start_date" required></label>
                        <label>Planned end date*<input type="date" name="end_date" required></label>
                    </div>
                </div>
            </fieldset>
        </div>

        <div class="card">
            <fieldset>
                <legend>5. Funding</legend>
                <label>Estimated amount (EUR)
                    <input type="text" name="estimated_amount">
                </label>
                <label>Compensation type
                    <select name="funding_type">
                        <option value="">Select...</option>
                        <option value="invoice">Invoice / cash</option>
                        <option value="in-kind">In-kind benefit (discount, barter, package)</option>
                    </select>
                </label>
                <label>Funding sources</label>
                <div class="inline-checkbox">
                    <label><input type="checkbox" name="funding_sources[]" value="public"> Public funds</label>
                    <label><input type="checkbox" name="funding_sources[]" value="private"> Private funds</label>
                </div>
                <label>Origin of funds
                    <select name="funding_origin_eu">
                        <option value="">Select...</option>
                        <option value="inside EU">Inside the EU</option>
                        <option value="outside EU">Outside the EU</option>
                        <option value="both">Both</option>
                    </select>
                </label>
            </fieldset>
        </div>

        <div class="card">
            <fieldset>
                <legend>6. Targeting and ad delivery (personal data)</legend>
                <p class="small">
                    Personal-data targeting requires explicit consent, cannot use special categories of data for profiling,
                    and cannot target people known to be under voting age.
                </p>
                <label>Are personal data used for targeting or ad delivery?
                    <select name="targeting_personal_data">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </label>
                <label>If yes, targeted groups / parameters
                    <textarea name="targeting_parameters" rows="2"></textarea>
                </label>
                <label>Targeting goals and logic
                    <textarea name="targeting_goals" rows="2"></textarea>
                </label>
                <label>Personal data categories used
                    <textarea name="targeting_categories" rows="2"></textarea>
                </label>
                <label>Data sources (first-party, purchased lists, platforms)
                    <textarea name="targeting_data_sources" rows="2"></textarea>
                </label>
                <label>AI or automated optimization used
                    <input type="text" name="targeting_ai_use" placeholder="No / describe system">
                </label>
                <label>Targeting policy link
                    <input type="text" name="targeting_policy_link" placeholder="https://...">
                </label>
                <label>Rights/consent management link
                    <input type="text" name="targeting_rights_link" placeholder="https://...">
                </label>
            </fieldset>
        </div>

        <div class="card">
            <fieldset>
                <legend>7. Attestation</legend>
                <label>
                    <input type="checkbox" name="certified_ttpa" value="Yes" required>
                    I confirm that the information provided is accurate and that the political advertising complies with
                    the TTPA rules, including consent and targeting limitations.
                </label>
                <p class="small">
                    By submitting, you consent to the publisher storing this information for at least seven years.
                </p>
            </fieldset>

            <button type="submit">Submit details</button>
        </div>
    </form>
</div>
</body>
</html>
