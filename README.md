# TTPA Transparency Demo (PHP + SQLite)
  A small, open-source demo for implementing transparency notices and public registers under the EU’s Regulation (EU) 2024/900 on the transparency and targeting of political advertising
  (TTPA). Built with plain PHP and SQLite, no dependencies.

  ## Features
  - Public register of political ads
  - Per‑ad transparency notice with sponsor, funding, and targeting details
  - Admin interface for publishing and completing records
  - First‑time admin password setup (no default credentials)
  - CSV export

  ## Quick Start
  php -S localhost:8000 -t ttpa_en
  Open http://localhost:8000/

  ## First‑Time Admin Setup
  Visit http://localhost:8000/setup.php to set the admin password.
  - Username: admin
  - Password: set on first use (min 12 characters)

  ## Usage
  - Submit ad details: http://localhost:8000/ad-form.php
  - Manage and publish ads: http://localhost:8000/admin.php
  - Transparency notice: http://localhost:8000/transparency.php?id=ID

  ## Data Storage
  SQLite database is created at:
  ttpa_en/data/ttpa_ads.sqlite
  Ensure the data/ directory is writable by the PHP process.

  ## Configuration
  Update the editorial contact email in:
  ttpa_en/config.php
  EDITORIAL_EMAIL is displayed on public pages and is used to submit complaints.

  ## Project Structure
  ttpa_en/
    index.php
    ad-form.php
    register.php
    transparency.php
    admin.php
    login.php
    logout.php
    setup.php
    config.php
    data/ (SQLite database)

  ## Disclaimer
  This project is provided for informational purposes and does not constitute legal advice. You are responsible for ensuring compliance with Regulation (EU) 2024/900, GDPR, and any
  national election or campaign laws.

  ## License
  This project and demo is licensed under the MiT and is free to use and modify. 
