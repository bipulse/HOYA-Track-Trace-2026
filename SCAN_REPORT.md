# Hoya Track & Trace — Scan Report (Baseline for Pardot → SFMC Migration)

Status: 2026-02-09

## 1) What’s in the repository (technical assets)

### PHP / batch / sending
- `assets/snippets/phpimport/index.php`: Generates a per-customer pre-rendered HTML email (template + token replacements), can send via PHPMailer, and archives HTML to `archive/<date>/<country>/<customernumber>.html`.
- `assets/snippets/phpimport/config.php`: MySQL connection (credentials must be provided via env/private file; not committed).
- `assets/snippets/phpimport/functions.php`: Helpers (logging, table rendering, etc.).
- `tnt2.php`: Proof-of-concept/tooling to fetch Salesforce contacts/accounts (OAuth token) and then pull orders via JWT from `track-and-trace.hoyailog.com` and write them into a DB.
- (Added from FTP) `tracking.php` / `tracking2.php` / `tracking_seiko_fr.php`: Server endpoints that render “precompiled HTML” based on `cid` (Salesforce AccountId) and language `l` (including DB queries for orders + translation).

### Templates / content
- `assets/snippets/phpimport/templates/`: HTML email templates (e.g. `template_hoyafr.html`, `template_seiko.html`, other variants)
- Token replacement uses `str_replace`, e.g. `{customernumber}`, `{unsubscribe-url}`, `{maildate}`, `{block1}`, etc.

### Tracking & compliance
- Open-tracking pixel: `assets/snippets/phpimport/templates/images/pixel.php`
  - Returns a 1x1 pixel image, then inserts into DB table `mail_statistics` (ip/referrer/useragent/browser)
  - The pixel URL is referenced by templates (example from code):
    - `.../templates/images/pixel.php?t={maildate}&cid={customernumber}`
- Unsubscribe endpoint: `assets/snippets/phpimport/unsubscribe/unsubscribe.php`
  - Expects POST field `EmailAddress`
  - Writes to DB table `unsubscribe (customeremail,time)`
  - `index.php` filters recipients using `where customeremail not in (SELECT distinct(customeremail) FROM unsubscribe)`

### Local/DB entities (derived from code)
- `unsubscribe`
- `mail_statistics`
- `my_log`
- `translation`
- `hoya_daily_data_*` (e.g. `hoya_daily_data_fr`; in `tnt2.php` also `hoya_daily_data_<countrycode>`)

## 2) Business process (extracted from email communication)

Source: internal email threads (intentionally excluded from this repository; see `.gitignore`).

Summary:
- T&T sends **daily order status emails** to ECPs who have **opted-in**.
- The opt-in flag lives on the **Salesforce Account**.
- Batch job:
  1. Collect Salesforce Account IDs + language + email addresses for enrolled ECPs.
  2. Fetch per-account pre-rendered HTML (server-side page) based on Account ID and language.
  3. Send emails (historically Sendgrid is mentioned; in this repo SMTP/PHPMailer is implemented).

Volume:
- Up to **10,000** recipients; sending **6 days/week** (mentioned as an assumption in the debrief).

## 3) Key technical dependencies / integrations

### Salesforce
- OAuth/access token flow and connection to the “Waeg SF instance”.
- In [emails/Status and hard deadlines.eml](emails/Status%20and%20hard%20deadlines.eml) the steps are outlined:
  - Authenticate against Salesforce (Client ID)
  - Collect IDs where the T&T flag is set
  - Generate a per-client token and authenticate against the T&T webservice per client, fetch orders, store temporarily in DB
- In [emails/RE- Order overzicht.eml](emails/RE-%20Order%20overzicht.eml) it is clarified:
  - The URL expects an **Account Id (starts with 001)**, not a Contact Id (003).

### Track & Trace API (iLog)
- `track-and-trace.hoyailog.com` is called via JWT (HS256).
- Note from the email thread: potential **IP blocking** of the webservice (to be verified).

### “Precompiled HTML” / tracking page
- Debrief emails mention examples like:
  - `https://www.hoyavision-service.com/tracking.php?cid=<ACCOUNT_ID>&l=nl-nl`
- Note: the page should be “only accessible for the T&T application” (access control).

Local testing hint:
- `tracking.php?preview=1` renders a template preview without DB access (for quick HTML/layout testing).

## 4) Risks / findings

- **Secrets/credentials were hardcoded historically** (DB, SMTP, OAuth/JWT). For the SFMC migration, these must be moved into secret management / environment configuration.
- Tracking/unsubscribe is currently **custom** (own DB tables + pixel + custom unsubscribe endpoint). In SFMC this is typically handled via **standard tracking/unsub** (or a preference center).

## 5) Relevance for Pardot → Marketing Cloud (SFMC)

What needs to be represented in SFMC (technical/business):
- Recipient/opt-in source of truth (Salesforce Account field) + synchronization to SFMC (Contact/SubscriberKey).
- Multilingualism (Language field → Locale/content variants).
- Daily batch/automation: data retrieval + content generation + send.
- Link/tracking URL strategy (tracking page/order overview) including AccountId as a parameter.
- Unsubscribe/preferences including legal texts (privacy/terms).

## 6) Open questions (for the next phase)

1. What is currently part of **Pardot** vs. what is already running outside (PHP/Sendgrid/SMTP)?
2. Should SFMC only take over **sending/tracking/preferences**, or also the **HTML generation** (dynamic content/data extensions)?
3. What is the desired **SubscriberKey** in SFMC (AccountId `001...` is a logical choice, fits the URL logic)?
4. Where do order data come from in the target picture:
   - still iLog API + own DB,
   - or directly/indirectly via Salesforce/integration?
5. Which business units/regions/brands (HOYA vs SEIKO) are part of the scope?

