<?php
require 'config.php';
apply_security_headers();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>GitHub Installation & Disclaimer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { --ink:#1a1b1f; --muted:#5c5f6a; --accent:#df4d2a; --accent-dark:#b53b1f; --paper:#f7f2ed; --panel:#fff; --line:#e3d8cd; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:"Manrope","Noto Sans","Helvetica Neue",sans-serif; background:var(--paper); color:var(--ink); }
        .wrap { max-width: 900px; margin: 0 auto; padding: 2rem 1.2rem 3rem; }
        header { padding-bottom:1rem; border-bottom:3px solid var(--accent); margin-bottom:1.5rem; }
        header h1 { margin:0; font-size:1.9rem; font-family:"Spectral","Cormorant Garamond",serif; }
        header p { margin:.3rem 0 0; color:var(--muted); }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:10px; padding:1rem 1.2rem; margin-bottom:1rem; }
        h2 { margin-top:0; font-size:1.1rem; }
        ol, ul { margin: .4rem 0 0 1.2rem; color: var(--muted); }
        code { font-size:.85rem; background:#f1e8e1; padding:.1rem .3rem; border-radius:3px; }
        a { color:var(--accent-dark); }
        .note { font-size:.9rem; color:var(--muted); }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <h1>GitHub Installation & Disclaimer</h1>
        <p>Open-source setup guide and legal disclaimer for the TTPA demo.</p>
    </header>

    <div class="card">
        <h2>Install from GitHub</h2>
        <ol>
            <li>Clone the repository:<br><code>git clone &lt;repo-url&gt;</code></li>
            <li>Start the PHP server:<br><code>php -S localhost:8000 -t ttpa_en</code></li>
            <li>Open <code>http://localhost:8000/</code> in your browser.</li>
            <li>Set the admin password at <code>/setup.php</code> (username: <code>admin</code>).</li>
        </ol>
        <p class="note">The SQLite database is created at <code>ttpa_en/data/ttpa_ads.sqlite</code>. Ensure the folder is writable.</p>
    </div>

    <div class="card">
        <h2>Configuration tips</h2>
        <ul>
            <li>Update <code>EDITORIAL_EMAIL</code> in <code>ttpa_en/config.php</code> for your newsroom contact.</li>
            <li>Consider moving the database directory outside the web root for production hosting.</li>
            <li>Review GDPR obligations when handling personal data used for targeting.</li>
        </ul>
    </div>

    <div class="card">
        <h2>Disclaimer</h2>
        <p class="note">
            This project is provided for informational purposes and as an open-source demo of Regulation (EU) 2024/900.
            It does not constitute legal advice. You are responsible for ensuring compliance with applicable EU and
            national law, including GDPR and any election or campaign rules. Use at your own risk.
        </p>
    </div>

    <div class="note">
        Back to <a href="index.php">home</a>.
    </div>
</div>
</body>
</html>
