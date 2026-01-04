<?php
require 'config.php';
apply_security_headers();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>TTPA Transparency Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --ink:#1a1b1f;
            --muted:#5c5f6a;
            --accent:#df4d2a;
            --accent-dark:#b53b1f;
            --paper:#f7f2ed;
            --panel:#ffffff;
            --line:#e3d8cd;
            --shadow:0 18px 50px rgba(27, 22, 18, 0.15);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Manrope", "Noto Sans", "Helvetica Neue", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(1200px 600px at 12% -10%, #ffd8c8 0%, transparent 60%),
                radial-gradient(1000px 600px at 90% 0%, #fbe3b5 0%, transparent 55%),
                var(--paper);
        }
        .wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2.5rem 1.2rem 4rem;
        }
        header {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
            gap: 2rem;
            align-items: center;
            margin-bottom: 2rem;
        }
        .eyebrow {
            text-transform: uppercase;
            letter-spacing: .2em;
            font-size: .75rem;
            color: var(--muted);
        }
        h1 {
            font-family: "Spectral", "Cormorant Garamond", serif;
            font-size: clamp(2.3rem, 4vw, 3.4rem);
            margin: .4rem 0 1rem;
            line-height: 1.05;
        }
        .lead {
            font-size: 1.05rem;
            color: var(--muted);
            max-width: 36rem;
        }
        .hero-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1.8rem;
            box-shadow: var(--shadow);
        }
        .hero-card h2 {
            margin-top: 0;
            font-size: 1.1rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .hero-card p { margin: .4rem 0; color: var(--muted); }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .85rem;
            padding: .3rem .7rem;
            background: #fff2eb;
            border: 1px solid #ffd0c2;
            border-radius: 999px;
            color: #9f3a23;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 1.2rem 1.4rem;
        }
        .card h3 {
            margin: 0 0 .6rem;
            font-size: 1.05rem;
        }
        .card ul {
            margin: 0;
            padding-left: 1.1rem;
            color: var(--muted);
        }
        .card li { margin-bottom: .4rem; }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: .8rem;
            margin-top: 1.4rem;
        }
        .btn {
            background: var(--accent);
            color: #fff;
            border: 0;
            border-radius: 999px;
            padding: .7rem 1.3rem;
            font-size: .95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }
        .btn:hover { background: var(--accent-dark); }
        .btn.secondary {
            background: transparent;
            color: var(--accent-dark);
            border: 1px solid var(--accent);
        }
        .footer {
            margin-top: 2.5rem;
            font-size: .9rem;
            color: var(--muted);
        }
        @media (max-width: 900px) {
            header { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <div>
            <div class="eyebrow">EU Regulation 2024/900</div>
            <h1>Transparency and Targeting of Political Advertising (TTPA)</h1>
            <p class="lead">
                This open-source demo shows how a publisher can collect, store, and disclose the information
                required by the EU rules on political advertising transparency and targeting.
            </p>
            <div class="actions">
                <a class="btn" href="register.php">Open public register</a>
                <a class="btn secondary" href="ad-form.php">Submit ad details</a>
                <a class="btn secondary" href="github.php">GitHub install & disclaimer</a>
            </div>
        </div>
        <div class="hero-card">
            <span class="pill">Transparency notice ready</span>
            <h2>What this site provides</h2>
            <p>Clear labels, a detailed transparency notice, and an open register of political ads.</p>
            <p>Designed for quick setup with PHP + SQLite and no external dependencies.</p>
        </div>
    </header>

    <div class="grid">
        <div class="card">
            <h3>Labeling and notice</h3>
            <ul>
                <li>Each ad is marked as a political advertisement.</li>
                <li>The notice identifies the sponsor, payer, and campaign link.</li>
                <li>Publication period, amounts, benefits, and calculation method are listed.</li>
            </ul>
        </div>
        <div class="card">
            <h3>Funding origin</h3>
            <ul>
                <li>Public vs. private funding sources are recorded.</li>
                <li>Inside or outside the EU origin is disclosed.</li>
                <li>Additional benefits (discounts, barters) are described.</li>
            </ul>
        </div>
        <div class="card">
            <h3>Targeting safeguards</h3>
            <ul>
                <li>Explicit consent is required for personal-data targeting.</li>
                <li>No profiling with special categories of data.</li>
                <li>No targeting of people known to be under voting age.</li>
            </ul>
        </div>
        <div class="card">
            <h3>Online ad transparency</h3>
            <ul>
                <li>Targeting parameters, data categories, and AI use are explained.</li>
                <li>Links to targeting policy and rights interface are provided.</li>
                <li>Links to the EU online ads repository can be added.</li>
            </ul>
        </div>
    </div>

    <div class="footer">
        Compliance focus: Regulation (EU) 2024/900, Articles 11, 12, 13, 18, and 19. Information is retained for seven
        years. This demo is informational and does not replace legal advice.
    </div>
</div>
</body>
</html>
