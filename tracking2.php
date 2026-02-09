<?php

$TEMP_FOLDER = "/is/htdocs/wp12361830_UVP0TKM3PI/www/ftp/import/hoya/";
$ARCH_FOLDER = "/is/htdocs/wp12361830_UVP0TKM3PI/www/ftp/import/archive/hoya/";
$MAILS_FOLDER = "/is/htdocs/wp12361830_UVP0TKM3PI/www/ftp/import/archive/html/hoya/";
$csvFile = "Daily-track-and-trace-email-HOYA.csv";
$importDb = "hoya_daily_data_nl";
$serverpath = "/is/htdocs/wp12361830_UVP0TKM3PI/";
$scriptpath = "/home/webpages/lima-city/bipulse-kreativwerkstatt/hoyavisionservice/assets/snippets/phpimport/";

require_once "assets/snippets/phpimport/config.php";

/* --------------------------- Get random customer -------------------------- */

$sql = " SELECT * FROM " . $importDb . "
 ";

if ($_GET['cid'])
{
    $sql .= "where  sfid = '" . $_GET['cid'] . "' ";
    $cid = $_GET['cid'];
}
else
{
    $sql .= "GROUP BY customernumber ORDER BY RAND() ASC limit 0,1";
}


$result = mysqli_query($conn, $sql);

$counter = 1;

//while ($row = mysqli_fetch_array($result))

$row = mysqli_fetch_array($result);
//{

    if ($_GET['l'])
    {
        $sql1 = " SELECT * FROM translation where l = '" . $_GET['l'] . "'";
    }
    else
    {
        $sql1 = " SELECT * FROM translation where l = '" . $row['l'] . "'";
    }

    /* --------------------------- Build address block -------------------------- */

    $result1 = mysqli_query($conn, $sql1);
    
    $_lang = mysqli_fetch_assoc($result1);

   

    $customeraddress = generateAddressBlock($row, $_lang);

    //$customernumber = $cid;
    $customernumber = $row["sfid"];

    $message = "Data collected from customer: " . $customernumber;
    write_mysql_log($message, $customernumber, '1', $conn);

     $sql2 = " SELECT * FROM " . $importDb . "
WHERE sfid = '" . $customernumber . "' order by orderplanneddate";
    $result2 = mysqli_query($conn, $sql2);
    $row_cnt = mysqli_num_rows($result2);

    $blocks = generateDataTable($result2);

    #print_r($blocks);
    $message = $row_cnt . " orders collected from customer: " . $customernumber;
    write_mysql_log($message, $customernumber, '4', $conn);

    $template = file_get_contents($scriptpath . 'templates/template_hoyafr.html');

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

    echo $mailbody;

    $counter++;

//}
?>
