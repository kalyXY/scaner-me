QR Attendance & Exams MVP
=================================

Minimal PHP/MySQL setup for student QR scans, attendance, payments, and exam authorizations.

Requirements
------------
- Docker + Docker Compose (recommended)
- Or: PHP 8.0+, MySQL 8.0+, Composer (manual setup)

Setup
-----
1. Import schema:
   ```bash
   mysql -u root -p < /workspace/schema.sql
   ```
2. Configure database via environment variables (optional):
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_NAME=school_mvp`
   - `DB_USER=root`
   - `DB_PASS=yourpassword`
Docker Quickstart
-----------------
1. Start stack (app + MySQL):
   ```bash
   make up
   ```
2. Install PHP dependencies inside container:
   ```bash
   docker compose exec app composer install
   ```
3. Open `http://localhost:8080/dashboard.php`

Manual Setup (Alternative)
--------------------------
1. Install PHP deps:
   ```bash
   composer install
   ```
2. Serve the `public/` directory:
   ```bash
   php -S 0.0.0.0:8080 -t public
   ```

Usage
-----
- Scan endpoint: `GET /scan.php?uuid=STUDENT_UUID`
- Dashboard: `GET /dashboard.php`
- CSV export: `GET /export_csv.php?date=YYYY-MM-DD` or `?session_id=ID`
- Generate QR images in `assets/qr/`:
   ```bash
   php scripts/generate_qr.php
   ```

Notes
-----
- `scan.php` finds the current session for today and marks `present` or `late` using `late_after_minutes`.
- If the session is an exam, it checks `payments` for a `paid` record tied to the `exam_id` and writes/updates `exam_authorizations`.
- Adjust course sessions according to your calendar; seed data as needed.

