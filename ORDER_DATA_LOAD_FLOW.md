# Track & Trace – Order Data Load Flow (Legacy)

Open this file in VS Code and use **Markdown Preview** to see the diagram:

- Preview: `Cmd+Shift+V`
- Preview to side: `Cmd+K` then `V`

## Diagram (Mermaid)

```mermaid
flowchart LR
  %% Order data load + consumption (legacy baseline)

  subgraph SF[Salesforce]
    SF_OAuth["OAuth token (refresh_token)"]
    SF_SOQL["SOQL: eligible recipients\n(Contact/Account + flags + language + brand)"]
  end

  subgraph ILOG[iLog Track & Trace API]
    ILOG_JWT["JWT (HS256) per customer"]
    ILOG_API["Fetch orders/status"]
  end

  subgraph DB[MySQL (legacy DB)]
    DAILY["Daily data tables\n(hoya_daily_data_*, seiko_daily_data_*)"]
  end

  subgraph PHP[Legacy PHP on hoyavision-service]
    CRON["Daily scheduler / cron"]
    TNT["Import job: tnt.php"]
    RENDER["Renderer: tracking.php?cid=<AccountId>&l=<locale>"]
  end

  subgraph PARDOT[Pardot (Account Engagement)]
    ES["Engagement Studio / send logic"]
    EMAIL["Email template\n(static HTML + merge fields)"]
    LINK["Dynamic link field\nTrack_and_Trace_URL__c (synced from SFDC)"]
  end

  %% Load path
  CRON --> TNT
  TNT --> SF_OAuth --> SF_SOQL --> TNT
  TNT --> ILOG_JWT --> ILOG_API --> TNT
  TNT -->|"write"| DAILY

  %% Send + consume
  SF_SOQL -->|"sync fields"| LINK
  LINK --> EMAIL
  ES --> EMAIL
  EMAIL -->|"recipient clicks"| RENDER
  RENDER -->|"read"| DAILY
  RENDER -->|"HTML order overview"| USER["Recipient browser"]
```

## Fallback (plain text)

1. Cron triggers `tnt.php` (import job).
2. `tnt.php` authenticates to Salesforce (OAuth) and queries eligible recipients (SOQL).
3. For each eligible account, `tnt.php` calls iLog Track & Trace API using a JWT.
4. Orders/status are written into MySQL daily tables (`hoya_daily_data_*` / `seiko_daily_data_*`).
5. Pardot sends an email that contains a dynamic link (`Track_and_Trace_URL__c` synced from Salesforce).
6. Recipient clicks the link → `tracking.php` renders the page by reading from the daily tables.
