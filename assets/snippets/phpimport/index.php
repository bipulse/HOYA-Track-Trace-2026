<?php

/**
 * @author bipulse - kreativwerkstatt
 * @email info@bipulse.de
 * @create date 2020-12-17 18:31:31
 * @modify date 2020-12-17 19:17:29
 * @desc [description]
 */


/* -------------------------------------------------------------------------- */
/*                           Configuration paramters                          */
/* -------------------------------------------------------------------------- */


$TEMP_FOLDER    = "/is/htdocs/wp12361830_UVP0TKM3PI/www/ftp/import/hoya/";
$ARCH_FOLDER    = "/is/htdocs/wp12361830_UVP0TKM3PI/www/ftp/import/archive/hoya/";
$MAILS_FOLDER   = "/is/htdocs/wp12361830_UVP0TKM3PI/www/ftp/import/archive/html/hoya/";
$csvFile        = "Daily-track-and-trace-email-HOYA.csv";   
$importDb       = "hoya_daily_data_fr";


require_once "config.php";
include 'PHPMailer/PHPMailer.php';
include 'PHPMailer/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="/assets/templates/modern/dist/css/style.css" rel="stylesheet">

    <style type="text/css">
  
  .table-striped tbody tr:nth-of-type(odd) {
      background-color: #F0F2F4;
      }
  
      .table-striped tbody tr:nth-of-type(even) {
      background-color: #E5E7E9 ;
      }

      .table tr th{
          font-weight:bold
      }
    </style>  
</head>
<body>
<div class="container">';


 




#mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


/* -------------------------------------------------------------------------- */
/*                                 Import CSV                                 */
/* -------------------------------------------------------------------------- */

if($_GET['import']){
/* --------------------------- Truncate data table -------------------------- */
    
    $sql = "TRUNCATE TABLE `hoya_daily_data_fr`;";
    $result = mysqli_query($conn, $sql);

/* ------------------------ Load CSV file into table ------------------------ */

    $sql = "LOAD DATA LOCAL INFILE '" . $TEMP_FOLDER . $csvFile."' INTO TABLE ".$importDb." FIELDS TERMINATED BY ';' IGNORE 1 ROWS ";
    $result = mysqli_query($conn, $sql);
    #$message = mysql_affected_rows();
    $message = sprintf("%s\n", mysqli_info($conn));
    write_mysql_log($message, '1', $conn);
   
    #$r =  mysqli_get_warnings($conn);

    #print_r($r);
    
    #write_mysql_log($r->message, $conn);

    #printf("%s\n", mysqli_info($conn));


    #rename($TEMP_FOLDER.$csvFile,$ARCH_FOLDER."hoya/".$csvFile);

// Moving file to New directory  

$newCsvFile = basename($TEMP_FOLDER.$csvFile, ".csv").date("_Y-m-d_H:i",time()).".csv";
if(copy($TEMP_FOLDER.$csvFile, $ARCH_FOLDER.$newCsvFile)) { 
    $message = "File $newCsvFile moved to archive after import."; 
write_mysql_log($message, '1', $conn);
} 
else { 
    $message = "File $newCsvFile could not be created."; 
write_mysql_log($message, '2', $conn);
} 
  
/* ------------------------------ Format dates ------------------------------ */

    $sql = "UPDATE ".$importDb." SET 
    orderdate = DATE_FORMAT(STR_TO_DATE(orderdate,'%d.%m.%Y' ),'%Y-%m-%d' ),
    orderplanneddate = DATE_FORMAT(STR_TO_DATE(orderplanneddate,'%d.%m.%Y' ),'%Y-%m-%d' );";
    $result = mysqli_query($conn, $sql);

}

/* -------------------------------------------------------------------------- */
/*                                 Get orders                                 */
/* -------------------------------------------------------------------------- */


/* --------------------------- Get random customer -------------------------- */

$sql = " SELECT * FROM ".$importDb."
where customeremail not in (SELECT distinct(customeremail) FROM unsubscribe) ";

if($_GET['cid']){
    $sql .= "and customernumber = '".$_GET['cid']."' ";
}
 $sql .= "GROUP BY customernumber ORDER BY RAND() LIMIT 0,1";
$result = mysqli_query($conn, $sql);

$counter = 1;

while ($row = mysqli_fetch_array($result)) {

    



    // echo "<br />Example ".$counter.":<br />".$customeraddress = generateAddressBlock($row);
    // echo "<br /><br />";

/* -------------------------- Get customer language ------------------------- */

    
    if($_GET['l']){
        $sql1 = " SELECT * FROM translation where lang = '".$_GET['l']."'";
    }else{
        $sql1 = " SELECT * FROM translation where lang = '".$row['l']."'";
    }
    

    /* --------------------------- Build address block -------------------------- */
    $customeraddress = generateAddressBlock($row,$_lang);

    $result1 = mysqli_query($conn, $sql1);
    $_lang = mysqli_fetch_assoc($result1);

    $customernumber = $row["customernumber"];
  
     $message = "Data collected from customer: ".$customernumber;
    write_mysql_log($message, $customernumber,'1', $conn);

    

    $sql2 = " SELECT * FROM ".$importDb." WHERE customernumber = ".$customernumber;
    $result2 = mysqli_query($conn, $sql2);
    $row_cnt = mysqli_num_rows($result2);

    $blocks = generateDataTable($result2);
    
    #print_r($blocks);

    $message = $row_cnt." orders collected from customer: ".$customernumber;
    write_mysql_log($message,$customernumber, '1', $conn);



/* -------------------------------------------------------------------------- */
/*                                 Send Emails                                */
/* -------------------------------------------------------------------------- */



$template = file_get_contents('templates/template_hoyafr.html');
$searchReplace = array(
        '{customernumber}'      => $customernumber,
        '{content}'             => $output,
        '{subject}'             => utf8_encode($_lang['subject']),
        '{dear-customer}'       => "<p><strong>".utf8_encode($_lang['dear-customer-1'])."</strong></p><p>".utf8_encode($_lang['dear-customer-2'])."</p><p><strong>".utf8_encode($_lang['dear-customer-3'])."</strong></p>",
        '{customeraddress}'     => $customeraddress,
        '{note}'     => utf8_encode($_lang['note']),
        '{new-planned-delivery-dates}' => utf8_encode($_lang['new-planned-delivery-dates']),
        '{new-planned-delivery-dates-text}' => utf8_encode($_lang['new-planned-delivery-dates-text']),
        '{deliveries-in-the-coming-3-days}' => utf8_encode($_lang['deliveries-in-the-coming-3-days']),
        '{deliveries-in-the-coming-3-days-text}' => utf8_encode($_lang['deliveries-in-the-coming-3-days-text']),
        '{all-open-orders}'     => utf8_encode($_lang['all-open-orders']),
        '{all-open-orders-text}' => utf8_encode($_lang['all-open-orders-text']),
        '{block1}'              => $blocks[0][0],
        '{block2}'              => $blocks[1][0],
        '{block3}'              => $blocks[2][0],
        '{block1mobile}'              => $blocks[0][1],
        '{block2mobile}'              => $blocks[1][1],
        '{block3mobile}'              => $blocks[2][1],
        '{order-ref}'          => utf8_encode($_lang['order-ref']),
        '{design}'              => utf8_encode($_lang['design']),
        '{order-date}'          => utf8_encode($_lang['order-date']),
        '{planned-date}'            => utf8_encode($_lang['planned-date']),
        '{end-consumer}'    => utf8_encode($_lang['end-consumer']),
        '{other-services}'    => utf8_encode($_lang['other-services']),
        '{badge1-img}'          => utf8_encode($_lang['badge1-img']),
        '{badge2-img}'          => utf8_encode($_lang['badge2-img']),
        '{badge3-img}'          => utf8_encode($_lang['badge3-img']),
        '{badge4-img}'          => utf8_encode($_lang['badge4-img']),
        '{badge1-link}'          => utf8_encode($_lang['badge1-link']),
        '{badge2-link}'          => utf8_encode($_lang['badge2-link']),
        '{badge3-link}'          => utf8_encode($_lang['badge3-link']),
        '{badge4-link}'          => utf8_encode($_lang['badge4-link']),
        '{contact-tel}'          => utf8_encode($_lang['contact-tel']),
        '{contact-email}'=> utf8_encode($_lang['contact-email']),
        '{opening}'=> utf8_encode($_lang['opening']),
        '{company-address}' => utf8_encode($_lang['company-address']),
        '{terms-of-use}'=> utf8_encode($_lang['terms-of-use']),
        '{terms-of-use-url}'=> utf8_encode($_lang['terms-of-use-url']),
        '{privacy-notice}'=> utf8_encode($_lang['privacy-notice']),
        '{privacy-notice-url}'=> utf8_encode($_lang['privacy-notice-url']),
        '{unsubscribe}'=> utf8_encode($_lang['unsubscribe']),
        '{unsubscribe-url}'=> utf8_encode($_lang['unsubscribe-url']),
        '{customeremail}'=> $row['customeremail'],
        '{maildate}' => time()
	);
    $mailbody = utf8_decode(str_replace(array_keys($searchReplace), array_values($searchReplace), $template));

    echo utf8_encode($mailbody);

#print_r($blocks);

    if($_GET['mail']){


            //Create a new PHPMailer instance
        $mail = new PHPMailer();


        $mail->IsSMTP();
        $mail->Host       = getenv('TNT_SMTP_HOST') ?: 'wp12361830.mailout.server-he.de';
        $mail->SMTPDebug  = 0; // Kann man zu debug Zwecken aktivieren
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('TNT_SMTP_USER') ?: '';
        $mail->Password   = getenv('TNT_SMTP_PASS') ?: '';
        $mail->SMTPSecure = getenv('TNT_SMTP_SECURE') ?: 'tls';
        $mail->Port = (int)(getenv('TNT_SMTP_PORT') ?: 587);

        if ($mail->Username === '' || $mail->Password === '') {
            echo "<p>SMTP not configured. Set TNT_SMTP_* environment variables.</p>";
            exit;
        }
        
        //Set who the message is to be sent from
        $mail->setFrom('noreply@hoyavision-service.com', $_lang['sender']);
        //Set an alternative reply-to address
        #$mail->addReplyTo('info@bipulse.de', 'First Last');
        //Set who the message is to be sent to
        
        // Production
        #$mail->addAddress($row['customeremail'], $row['customername1']);

        // Development
        if($_GET['test'])$mail->addAddress('bipulse+default@precheck.emailonacid.com', 'Sascha Bien');
        $mail->addAddress('info@bipulse.de', 'Sascha Bien');
        #$mail->addAddress('allaya.yasmine@gmail.com', 'Yasmine');
        #$mail->addAddress('info@bipulse.de', 'Sascha Bien');
       #$mail->addAddress('nduval@hoya.fr', 'Nicolas Duval');
       # $mail->addAddress('allaya@hoya.com', 'Yasmine Allaya');
        
        
        
        #$mail->addAddress('sascha.bien@gmail.com', 'Sascha Bien 2'); 
        //Set the subject line
        $mail->Subject = $_lang['subject'];

        #$mail->Subject = $_lang['subject'];

        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $mail->msgHTML($mailbody);
        //Replace the plain text body with one created manually
        #$mail->AltBody = 'This is a plain-text message body';
        //Attach an image file
        #$mail->addAttachment('images/phpmailer_mini.png');

        //send the message, check for errors
        if (!$mail->send()) {
            write_mysql_log($mail->ErrorInfo, $customernumber,'2', $conn);
            #echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            write_mysql_log('Notification sent to customernumer '.str_pad($row['customernumber'], 5, '0', STR_PAD_LEFT)." (".$row['customeremail'].').',$customernumber, '1', $conn);
        }



    }


    $subfolder = date("Y-m-d");
    
    $folderName = "archive/".$subfolder."/".$row['customercountry'];
    if(!is_dir($folderName))
		{
			mkdir($folderName, 0777);
		}

    
    $myFile = $folderName."/".$row['customernumber'].".html"; // or .php   
    $fp = fopen($myFile,'w+');
    fwrite($fp, utf8_encode($mailbody));
    fclose($fp);
    
    $counter++;

 }

/* --------------------------- Get customer orders -------------------------- */

#


 
/* -------------------------------------------------------------------------- */
/*                                 Send Emails                                */
/* -------------------------------------------------------------------------- */



#$mailbody = file_get_contents('templates/template_hoyafr.php');
#$mailbody = $output;













echo '</div></body>
</html>';
?>