<?php

header('Content-Type: text/html; charset=UTF-8');

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

$brand = isset($_GET['brand']) ? strtolower((string)$_GET['brand']) : '';



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
    $_lang = [
        'subject' => 'Track & Trace – Preview',
        'dear-customer-1' => 'Dear customer,',
        'dear-customer-2' => 'this is a local preview of the precompiled HTML.',
        'dear-customer-3' => 'Kind regards',
        'note' => 'Preview mode (no database).',
        'new-planned-delivery-dates' => 'New planned delivery dates',
        'new-planned-delivery-dates-text' => 'Orders with changed planned delivery dates.',
        'deliveries-in-the-coming-3-days' => 'Deliveries in the coming 3 days',
        'deliveries-in-the-coming-3-days-text' => 'Orders expected soon.',
        'all-open-orders' => 'All open orders',
        'all-open-orders-text' => 'Overview of open orders.',
        'new-orders' => 'New orders',
        'new-orders-text' => 'Newly created orders.',
        'shipped-orders' => 'Shipped orders',
        'shipped-orders-text' => 'Recently shipped orders.',
        'order-ref' => 'Order reference',
        'design' => 'Design',
        'order-date' => 'Order date',
        'planned-date' => 'Planned date',
        'end-consumer' => 'End consumer',
        'other-services' => 'Other services',
        'badge1-img' => '',
        'badge2-img' => '',
        'badge3-img' => '',
        'badge4-img' => '',
        'badge1-link' => '#',
        'badge2-link' => '#',
        'badge3-link' => '#',
        'badge4-link' => '#',
        'contact-tel' => '+00 000 0000',
        'contact-email' => 'support@example.com',
        'opening' => 'Mon–Fri 09:00–17:00',
        'company-address' => 'Example Address',
        'terms-of-use' => 'Terms of use',
        'terms-of-use-url' => '#',
        'privacy-notice' => 'Privacy notice',
        'privacy-notice-url' => '#',
        'unsubscribe' => 'Unsubscribe',
        'unsubscribe-url' => '/assets/snippets/phpimport/unsubscribe/unsubscribe.php?EmailAddress=',
        'footer' => '',
    ];

    $row = [
        'l' => $locale !== '' ? $locale : 'nl-nl',
        'customeremail' => 'test@example.com',
        'sfid' => $customernumber,
        'customernumber' => '12345',
        'customername1' => 'Example Company',
        'customeradress' => 'Example Street 1',
        'customerzip' => '12345',
        'customercity' => 'Example City',
    ];

    $customeraddress = '<p>'
        . 'Customer: ' . htmlspecialchars($customernumber, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '<br />'
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

    if ($locale !== '') {
        $sql1 = " SELECT * FROM translation where l = '" . $locale . "'";
    } else {
        $sql1 = " SELECT * FROM translation where l = '" . $row['l'] . "'";
    }

    $result1 = mysqli_query($conn, $sql1);
    $_lang = mysqli_fetch_assoc($result1);
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

    

    $searchReplace = array(
        '{customernumber}' => $customernumber,
        '{content}' => $output,
        '{subject}' => utf8_encode($_lang['subject']) ,
        '{dear-customer}' => "<p><strong>" . utf8_encode($_lang['dear-customer-1']) . "</strong></p><p>" . utf8_encode($_lang['dear-customer-2']) . "</p><p><strong>" . utf8_encode($_lang['dear-customer-3']) . "</strong></p>",
        '{customeraddress}' => $customeraddress,
        '{note}' => utf8_encode($_lang['note']) ,
        '{new-planned-delivery-dates}' => utf8_encode($_lang['new-planned-delivery-dates']) ,
        '{new-planned-delivery-dates-text}' => utf8_encode($_lang['new-planned-delivery-dates-text']) ,
        '{deliveries-in-the-coming-3-days}' => utf8_encode($_lang['deliveries-in-the-coming-3-days']) ,
        '{deliveries-in-the-coming-3-days-text}' => utf8_encode($_lang['deliveries-in-the-coming-3-days-text']) ,
        '{all-open-orders}' => utf8_encode($_lang['all-open-orders']) ,
        '{all-open-orders-text}' => utf8_encode($_lang['all-open-orders-text']) ,
        '{new-orders}' => utf8_encode($_lang['new-orders']) ,
        '{new-orders-text}' => utf8_encode($_lang['new-orders-text']) ,
        '{shipped-orders}' => utf8_encode($_lang['shipped-orders']) ,
        '{shipped-orders-text}' => utf8_encode($_lang['shipped-orders-text']) ,
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
        '{order-ref}' => utf8_encode($_lang['order-ref']) ,
        '{design}' => utf8_encode($_lang['design']) ,
        '{order-date}' => utf8_encode($_lang['order-date']) ,
        '{planned-date}' => utf8_encode($_lang['planned-date']) ,
        '{end-consumer}' => utf8_encode($_lang['end-consumer']) ,
        '{other-services}' => utf8_encode($_lang['other-services']) ,
        '{badge1-img}' => utf8_encode($_lang['badge1-img']) ,
        '{badge2-img}' => utf8_encode($_lang['badge2-img']) ,
        '{badge3-img}' => utf8_encode($_lang['badge3-img']) ,
        '{badge4-img}' => utf8_encode($_lang['badge4-img']) ,
        '{badge1-link}' => utf8_encode($_lang['badge1-link']) ,
        '{badge2-link}' => utf8_encode($_lang['badge2-link']) ,
        '{badge3-link}' => utf8_encode($_lang['badge3-link']) ,
        '{badge4-link}' => utf8_encode($_lang['badge4-link']) ,
        '{contact-tel}' => utf8_encode($_lang['contact-tel']) ,
        '{contact-email}' => utf8_encode($_lang['contact-email']) ,
        '{opening}' => utf8_encode($_lang['opening']) ,
        '{company-address}' => utf8_encode($_lang['company-address']) ,
        '{terms-of-use}' => utf8_encode($_lang['terms-of-use']) ,
        '{terms-of-use-url}' => utf8_encode($_lang['terms-of-use-url']) ,
        '{privacy-notice}' => utf8_encode($_lang['privacy-notice']) ,
        '{privacy-notice-url}' => utf8_encode($_lang['privacy-notice-url']) ,
        '{unsubscribe}' => utf8_encode($_lang['unsubscribe']) ,
        '{unsubscribe-url}' => utf8_encode($_lang['unsubscribe-url']) ,
        '{customeremail}' => $row['customeremail'],
        '{maildate}' => time() ,
        '{footer}' => utf8_encode($_lang['footer']) ,
    );
    $mailbody = (str_replace(array_keys($searchReplace) , array_values($searchReplace) , $template));

    // In preview mode, remove any unreplaced {tokens} for a clean output.
    if ($preview) {
        $mailbody = preg_replace('/\{[^\}]+\}/', '', (string)$mailbody);
    }

    echo $mailbody;

?>
