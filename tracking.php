<?php

header('Content-Type: text/html; charset=UTF-8');

require_once __DIR__ . '/assets/snippets/phpimport/i18n.php';

$domain_name = $_SERVER['HTTP_HOST'] ?? '';

$baseDir = __DIR__;
$scriptpathLocal = $baseDir . '/assets/snippets/phpimport/';

$TEMP_FOLDER = "/is/htdocs/wp12361830_UVP0TKM3PI/www/ftp/import/hoya/";
$ARCH_FOLDER = "/is/htdocs/wp12361830_UVP0TKM3PI/www/ftp/import/archive/hoya/";
$MAILS_FOLDER = "/is/htdocs/wp12361830_UVP0TKM3PI/www/ftp/import/archive/html/hoya/";
$csvFile = "Daily-track-and-trace-email-HOYA.csv";
$importDb = "hoya_daily_data_nl";
$serverpath = "/is/htdocs/wp12361830_UVP0TKM3PI/";
$scriptpath = $scriptpathLocal;

$preview = isset($_GET['preview']) && $_GET['preview'] !== '0';

$cid = isset($_GET['cid']) ? (string)$_GET['cid'] : '';
// Allow Salesforce 15/18 char IDs + common safe chars.
if ($cid !== '' && !preg_match('/^[A-Za-z0-9]{10,25}$/', $cid)) {
    $cid = '';
}

$locale = isset($_GET['l']) ? (string)$_GET['l'] : '';
if ($locale !== '' && !preg_match('/^[A-Za-z]{2}-[A-Za-z]{2}$/', $locale)) {
    $locale = '';
}

$localeDb = tnt_normalize_locale_db($locale);

$brand = isset($_GET['brand']) ? strtolower((string)$_GET['brand']) : '';

// Legacy token used by some templates.
$output = '';



// Decide brand/db-config either by explicit query param or by host.
if ($brand === 'seiko' || $domain_name === 'services.seikovision.com') {
    $importDb = "seiko_daily_data_fr";
    if (!$preview) {
        require_once "assets/snippets/phpimport/config-seiko.php";
    }
} else {
    $importDb = "hoya_daily_data_nl";
    if (!$preview) {
        require_once "assets/snippets/phpimport/config.php";
    }
}



if ($preview) {
    $customernumber = $cid !== '' ? $cid : '001000000000000AAA';
    $effectiveLocaleDb = $localeDb !== '' ? $localeDb : 'nl-nl';
    $_lang = tnt_lang_for_render($effectiveLocaleDb, [
        'subject' => 'Track & Trace – Preview',
        'note' => 'Preview mode (no database).',
    ]);

    $row = [
        'l' => $effectiveLocaleDb,
        'customeremail' => 'test@example.com',
        'sfid' => $customernumber,
        'customernumber' => '12345',
        'customername1' => 'Example Company',
        'customeradress' => 'Example Street 1',
        'customerzip' => '12345',
        'customercity' => 'Example City',
    ];

    $customerLabel = tnt_ensure_utf8((string)($_lang['customernumber'] ?? 'Customer'));
    $customeraddress = '<p>'
        . htmlspecialchars($customerLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ': '
        . htmlspecialchars($customernumber, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '<br />'
        . htmlspecialchars($row['customername1'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '<br />'
        . htmlspecialchars($row['customeradress'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '<br />'
        . htmlspecialchars($row['customerzip'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' '
        . htmlspecialchars($row['customercity'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        . '</p>';

    $sampleRow = function (): string {
        return ''
            . "<tr bgcolor=\"#F0F2F4\" style=\"background-color:#F0F2F4;font-family:sans-serif\">"
            . "<td align=\"left\">123456<br />John Doe</td>"
            . "<td align=\"left\">Example Lens</td>"
            . "<td align=\"center\">" . date('d/m/Y', strtotime('+2 days')) . "</td>"
            . "<td align=\"center\"><strong>" . date('d/m/Y') . "</strong></td>"
            . "</tr>";
    };

    $blocks = [];
    for ($i = 0; $i <= 4; $i++) {
        $blocks[$i] = [$sampleRow(), $sampleRow()];
    }
} else {
    /* --------------------------- Get random customer -------------------------- */

    $sql = " SELECT * FROM " . $importDb . "\n ";

    if ($cid !== '') {
        $sql .= "where  sfid = '" . $cid . "' ";
    } else {
        $sql .= "GROUP BY customernumber ORDER BY RAND() ASC limit 0,1";
    }

    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);

    if (!$row) {
        http_response_code(404);
        echo "No customer found (DB query returned no rows).";
        exit;
    }

    $effectiveLocaleDb = $localeDb !== '' ? $localeDb : tnt_normalize_locale_db((string)($row['l'] ?? ''));
    if ($effectiveLocaleDb !== '') {
        $sql1 = " SELECT * FROM translation where l = '" . $effectiveLocaleDb . "'";
    } else {
        $sql1 = " SELECT * FROM translation where l = '" . $row['l'] . "'";
    }

    $result1 = mysqli_query($conn, $sql1);
    $dbLang = mysqli_fetch_assoc($result1);
    $_lang = tnt_lang_for_render($effectiveLocaleDb !== '' ? $effectiveLocaleDb : (string)($row['l'] ?? ''), is_array($dbLang) ? $dbLang : []);
    $customeraddress = generateAddressBlock($row, $_lang);

    $customernumber = $row["sfid"];

    $message = "Data collected from customer: " . $customernumber;
    write_mysql_log($message, $customernumber, '1', $conn);

    $sql2 = " SELECT * FROM " . $importDb . "\nWHERE sfid = '" . $customernumber . "' order by orderplanneddate,orderdate asc";
    $result2 = mysqli_query($conn, $sql2);
    $row_cnt = mysqli_num_rows($result2);

    $blocks = generateDataTable($result2);

    $message = $row_cnt . " orders collected from customer: " . $customernumber;
    write_mysql_log($message, $customernumber, '4', $conn);
}

switch($domain_name) {
    case 'hoyavision-service.com':
        $template = file_get_contents($scriptpath . 'templates/template_hoyafr.php');
        break;
    case 'services.seikovision.com':
        $template = file_get_contents($scriptpath . 'templates/templates_seikofr.php');
        break;
    default:
        $template = file_get_contents($scriptpath . 'templates/template_hoyafr.php');
}

if ($brand === 'seiko' && is_file($scriptpath . 'templates/templates_seikofr.php')) {
    $template = file_get_contents($scriptpath . 'templates/templates_seikofr.php');
}

    

    $searchReplace = [
        '{customernumber}' => $customernumber,
        '{content}' => $output,
        '{customeraddress}' => $customeraddress,

        // Blocks / tables
        '{block0}' => $blocks[3][0],
        '{block1}' => $blocks[0][0],
        '{block2}' => $blocks[1][0],
        '{block3}' => $blocks[2][0],
        '{block4}' => $blocks[4][0],
        '{block0mobile}' => $blocks[3][1],
        '{block1mobile}' => $blocks[0][1],
        '{block2mobile}' => $blocks[1][1],
        '{block3mobile}' => $blocks[2][1],
        '{block4mobile}' => $blocks[4][1],

        '{customeremail}' => $row['customeremail'] ?? '',
        '{maildate}' => time(),
        '{html-lang}' => tnt_normalize_locale_html($effectiveLocaleDb ?? ($row['l'] ?? $localeDb ?? '')),
    ];

    $searchReplace['{dear-customer}'] = "<p><strong>"
        . tnt_ensure_utf8((string)($_lang['dear-customer-1'] ?? ''))
        . "</strong></p><p>"
        . tnt_ensure_utf8((string)($_lang['dear-customer-2'] ?? ''))
        . "</p><p><strong>"
        . tnt_ensure_utf8((string)($_lang['dear-customer-3'] ?? ''))
        . "</strong></p>";

    // Make all translation keys usable as {tokens} in templates.
    foreach ($_lang as $key => $value) {
        if (!is_string($key) || $key === '') continue;
        if (!is_scalar($value) && $value !== null) continue;

        $token = '{' . $key . '}';
        if (array_key_exists($token, $searchReplace)) continue;
        $searchReplace[$token] = tnt_ensure_utf8($value === null ? '' : (string)$value);
    }
    $mailbody = (str_replace(array_keys($searchReplace) , array_values($searchReplace) , $template));

    // In preview mode, remove any unreplaced {tokens} for a clean output.
    if ($preview) {
        $mailbody = preg_replace('/\{[^\}]+\}/', '', (string)$mailbody);
    }

    echo $mailbody;

?>
