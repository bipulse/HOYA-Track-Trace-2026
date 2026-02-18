# Hoya Track & Trace — Legacy Baseline (Pardot → SFMC Migration)

> Legacy baseline archive of HOYA Track & Trace PHP (Pardot-era) for data import, HTML rendering, and email sending; reference for SFMC migration.

This repository is primarily an **as-is archive / baseline** of the legacy PHP implementation.

It is **not** the future SFMC solution. The SFMC flow will be designed and implemented separately, using this repo as a reference for requirements and behavior.

This repository contains the legacy PHP implementation used to:

- import Track & Trace order/status data (Salesforce → iLog) into a MySQL database
- render “precompiled HTML” pages used for email content
- (historically) send emails via PHPMailer/SMTP

## What matters operationally

- **Cron/import endpoint**: `tnt.php`
  - Production URL: `https://www.hoyavision-service.com/tnt.php`
  - Local safe mode: `tnt.php?dryrun=1`

- **HTML render endpoint**: `tracking.php`
  - Typical production usage: `tracking.php?cid=<SF_ACCOUNT_ID>&l=<locale>`
  - Local preview mode (no DB): `tracking.php?preview=1`

Template selection is host-based:

- `hoyavision-service.com` → HOYA template
- `services.seikovision.com` → SEIKO template

## Credentials and secrets

This repo intentionally contains **no real credentials**.

Provide secrets via environment variables, or by creating ignored `*.private.php` files.

### Database (used by tracking/import scripts)

Environment variables:

- `TNT_DB_HOST` (default: `localhost`)
- `TNT_DB_USER`
- `TNT_DB_PASS`
- `TNT_DB_NAME`

Optional ignored file (not committed):

- `assets/snippets/phpimport/config.private.php`

### Salesforce OAuth + iLog JWT (used by `tnt.php` and some legacy scripts)

Environment variables:

- `SF_CLIENT_ID`
- `SF_CLIENT_SECRET`
- `SF_REFRESH_TOKEN`
- `SF_TOKEN_URL` (optional; defaults to the Hoya SF instance token endpoint)
- `ILOG_JWT_SECRET`

Optional ignored file (not committed):

- `tnt.private.php`

## Documentation

- Scan / archeology notes: [SCAN_REPORT.md](SCAN_REPORT.md)
- Legacy Full HTML Email flow (baseline): [FULL_HTML_EMAIL_FLOW.md](FULL_HTML_EMAIL_FLOW.md)

## Notes

- The `emails/` folder is excluded from git history and must not be committed.
