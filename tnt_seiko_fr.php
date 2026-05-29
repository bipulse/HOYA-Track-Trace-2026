<?php

require_once '/home/webpages/lima-city/bipulse-kreativwerkstatt/hoyavisionservice/assets/snippets/jwt/JWT.php';
require_once '/home/webpages/lima-city/bipulse-kreativwerkstatt/hoyavisionservice/assets/snippets/phpimport/config.php';
@include_once '/home/webpages/lima-city/bipulse-kreativwerkstatt/hoyavisionservice/assets/snippets/phpimport/config.private.php';

use Firebase\JWT\JWT;


$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://hoya.my.salesforce.com/services/oauth2/token",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
	CURLOPT_POSTFIELDS => http_build_query([
		'grant_type'    => 'refresh_token',
		'client_id'     => getenv('SF_CLIENT_ID')     ?: (defined('SF_CLIENT_ID')     ? SF_CLIENT_ID     : ''),
		'client_secret' => getenv('SF_CLIENT_SECRET') ?: (defined('SF_CLIENT_SECRET') ? SF_CLIENT_SECRET : ''),
		'refresh_token' => getenv('SF_REFRESH_TOKEN') ?: (defined('SF_REFRESH_TOKEN') ? SF_REFRESH_TOKEN : ''),
	]),
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/x-www-form-urlencoded"
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$response_data = json_decode($response, true);
$accessToken = $response_data['access_token'];



$payload = [
	"iss" => "salesforce",
	"sub"=> "Salesforce for T&T API access",
	"aud"=> "track-and-trace.hoyailog.com",
	"nbf"=> time(),
	"iat"=> time(),
	"exp"=> time() + 60*5
	];

// Set the secret key for signing the JWT
$secret_key = getenv('ILOG_JWT_SECRET') ?: (defined('ILOG_JWT_SECRET') ? ILOG_JWT_SECRET : '');

if ($secret_key === '') {
	echo 'Missing required environment variable: ILOG_JWT_SECRET.';
	exit;
}


// $query = urlencode("Select Id,Contact__r.Account.IsDeleted,Contact__r.Account.Name,Contact__r.Account.Id,Contact__r.Account.Hoya_Account_ID__c,Contact__r.Account.Brand__c,Contact__r.Account.Shop_Country__c,Contact__r.Account.Account_Number_ILog__c,Contact__r.Account.Shop_City__c,Contact__r.Account.Shop_Postal_Code__c, Contact__r.Account.Shop_State__c,Contact__r.Account.Shop_Street__c,Contact__r.Language_Pick__c  from Subscription__c Where Active_Subscription__c = true AND Contact__r.Account.Account_Number_ILog__c != null AND Contact__r.Account.Brand__c = 'HOYA' AND  Contact__r.Account.Shop_Country__c = 'NL'");
//$query = urlencode("Select Id,Account.IsDeleted,Account.Name,AccountId, Account.Hoya_Account_ID__c,Account.Brand__c,Account.Shop_Country__c,Account.Account_Number_ILog__c,Account.Shop_City__c,Account.Shop_Postal_Code__c, Account.Shop_State__c,Account.Shop_Street__c,Language_Pick__c from Contact Where Active_Subscription__c = true AND Account.Account_Number_ILog__c != null AND Account.Brand__c = 'SEIKO' AND Account.Shop_Country__c = 'FR' AND Subscription_Type__c ='Track and Trace' AND RecordType.developerName ='Subscription'");
$query = urlencode("Select Id,
    Account.IsDeleted,
    Account.Name,
    AccountId,
    Account.Hoya_Account_ID__c,
    Account.Brand__c,
    Account.Shop_Country__c,
    Account.Account_Number_ILog__c,
    Account.Shop_City__c,
    Account.Shop_Postal_Code__c,
    Account.Shop_State__c,
    Account.Shop_Street__c,
    Language_Pick__c,
    Track_and_Trace_URL__c
    From Contact
    Where Track_Trace_Subscription__c = true
    AND Account.Account_Number_ILog__c != null
    AND Account.Brand__c = 'SEIKO'
    AND Account.Shop_Country__c = 'FR'");
	
	$url = 'https://hoya.my.salesforce.com/services/data/v50.0/query/?q='.$query;

//$accessToken = "00Db0000000JkWX!AQoAQOErrPpik.y29YyvtSvLsZ0zUcbucoUognGS.N.JLvcZfmhdMXi87EXnRzm1BhlgXXxQxOtVFMrjvcz52wCC1f5bl37v";

$curl = curl_init($url);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

 $response = curl_exec($curl);

if (curl_errno($curl)) {
    echo 'Error: ' . curl_error($curl);
} else {
     $responseData = json_decode($response, true);
	$stores = $responseData['records'] ?? [];
}

curl_close($curl);

// Stores missing from Salesforce — add manually
$extraStores = [
    '001b000003gA5ZfAAK' => [
        'Account' => [
            'Account_Number_ILog__c' => '0464694',
            'Hoya_Account_ID__c'     => 'SO3300464694',
            'Shop_Country__c'        => 'FR',
            'Name'                   => '',
            'Shop_Street__c'         => '',
            'Shop_Postal_Code__c'    => '',
            'Shop_City__c'           => '',
        ],
        'AccountId' => '001b000003gA5ZfAAK',
    ],
];

$sfids = array_column($stores, 'AccountId');
foreach ($extraStores as $sfid => $extra) {
    if (!in_array($sfid, $sfids)) {
        $stores[] = $extra;
    }
}


?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>SEIKO FR — Order Overview</title>
    <style>
      body { font-family: Arial, sans-serif; font-size: 13px; color: #222; margin: 20px; }
      h1 { font-size: 18px; margin-bottom: 4px; }
      .meta { color: #666; font-size: 12px; margin-bottom: 24px; }
      .store { margin-bottom: 28px; page-break-inside: avoid; }
      .store-header { background: #1a3a5c; color: #fff; padding: 8px 12px; border-radius: 4px 4px 0 0; }
      .store-header .name { font-weight: bold; font-size: 14px; }
      .store-header .ids { font-size: 11px; opacity: .8; margin-top: 2px; }
      .store-address { background: #f0f4f8; padding: 6px 12px; font-size: 12px; color: #444; border: 1px solid #d0dae6; border-top: none; }
      .no-orders { padding: 8px 12px; font-style: italic; color: #999; border: 1px solid #d0dae6; border-top: none; border-radius: 0 0 4px 4px; }
      table { width: 100%; border-collapse: collapse; border: 1px solid #d0dae6; border-top: none; border-radius: 0 0 4px 4px; }
      th { background: #e8eef5; text-align: left; padding: 5px 10px; font-size: 11px; color: #555; border-bottom: 1px solid #d0dae6; }
      td { padding: 4px 10px; border-bottom: 1px solid #eef1f5; }
      tr:last-child td { border-bottom: none; }
      .delay-ok   { color: #2e7d32; font-weight: bold; }
      .delay-late { color: #c62828; font-weight: bold; }
      .delay-warn { color: #e65100; font-weight: bold; }
      @media print { body { margin: 10px; } .store { page-break-inside: avoid; } }
    </style>
  </head>
  <body>
    <h1>SEIKO France — Order Overview</h1>
    <div class="meta">Generated: <?php echo date('Y-m-d H:i'); ?> &nbsp;|&nbsp; Period: last 7 days</div>

	  <?php

$totalOrders = 0;
$storeData = [];

foreach($stores as $store){

	$ilogId =  ltrim($store['Account']['Account_Number_ILog__c'],"0");
	$countrycode = strtolower($store['Account']['Shop_Country__c']);
	$header = [
		"alg" => "HS256",
		"typ"=> "JWT",
		"mfc"=> "SEI",
		"cc"=> "sei",
		"cn"=> $ilogId
	];
	$jwt = JWT::encode($payload, $secret_key, 'HS256',null,$header);

	$curl = curl_init();
	$auth_token = $jwt;

	curl_setopt_array($curl, array(
	   CURLOPT_URL => "https://track-and-trace.hoyailog.com/api/orders?itemsPerPage=200&deliveryDate[after]=".date("Y-m-d", strtotime("-1 week"))."&manufacturer.code=sei",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array("Authorization: Bearer $auth_token"),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);

	if ($err) {
		echo '<p style="color:red">cURL Error for '.$ilogId.': '.htmlspecialchars($err).'</p>';
		continue;
	}

	$result = json_decode($response, true);
	$orders = $result['hydra:member'] ?? [];
	$totalOrders += count($orders);

	$name    = htmlspecialchars($store['Account']['Name'] ?? '');
	$street  = htmlspecialchars($store['Account']['Shop_Street__c'] ?? '');
	$zip     = htmlspecialchars($store['Account']['Shop_Postal_Code__c'] ?? '');
	$city    = htmlspecialchars($store['Account']['Shop_City__c'] ?? '');
	$clientNo = htmlspecialchars($store['Account']['Hoya_Account_ID__c']);
	$ilogNo   = htmlspecialchars($store['Account']['Account_Number_ILog__c']);

	echo '<div class="store">';
	echo '<div class="store-header">';
	echo '<div class="name">'.($name ?: $clientNo).'</div>';
	echo '<div class="ids">Client-No.: '.$clientNo.' &nbsp;|&nbsp; iLog-No.: '.$ilogNo.'</div>';
	echo '</div>';

	if ($street || $city) {
		echo '<div class="store-address">'.$street.($street && ($zip||$city) ? ', ' : '').$zip.' '.$city.'</div>';
	}

	if (empty($orders)) {
		echo '<div class="no-orders">No orders in selected period.</div>';
		echo '</div>';
	} else {
		echo '<table>';
		echo '<tr><th>Order No.</th><th>Order Date</th><th>Delivery Date</th><th>Delay</th></tr>';

	foreach($orders as $order){

	$orderDate = $order["orderDate"];
	$deliveryDate = $order["deliveryDate"];
	$orderNumber = 	$order["orderNumber"];
	$tt_station_id =$order["stationId"];
	$hoyailog_station_code = $order["stationLogistics"];
	$orderDelay =  ($order["delay"] > 0) ? "Y" : "N";
	$orderLensName = ($order['lensLeft'] == $order['lensRight']) ? $order['lensLeft'] : $order['lensLeft']." / ".$order['lensRight'];
	$ordconf = ($order['stationId'] != 14 && $order['stationId'] != 15) ? "Y" : "N";

		$sqlArray[] = "INSERT INTO seiko_daily_data_".$countrycode." (`customernumber`,`sfid`, `customername1`, `customername2`, `customeradress`, `customerzip`, `customercity`, `customercountry`, `l`, `customeremail`, `branchoffice`, `customergroup`, `ordconf`, `send_fax_em`, `orderdate`, `orderpatient`, `orderreference`, `orderplanneddate`, `orderupdateddate`, `orderstatut`, `orderlenstype`, `ordercoating`, `orderlab`, `orderlensname`, `ordernr`,`tt_station_id`,`hoyailog_station_code`) VALUES ('".addslashes($store['Account']['Hoya_Account_ID__c'])."','".addslashes($store['AccountId'])."', '".addslashes($store['Account']['Name'])."', '".addslashes($customername2)."', '".addslashes($store['Account']['Shop_Street__c'])."', '".addslashes($store['Account']['Shop_Postal_Code__c'])."', '".addslashes($store['Account']['Shop_City__c'])."', '".addslashes($customercountry)."', 'fr-fr', '".addslashes($customeremail)."', '', '', '".addslashes($ordconf)."', '', '".addslashes($orderDate)."', '".addslashes($order["reference1"])."', '".addslashes($order["reference2"])."', '".addslashes($deliveryDate)."', '".addslashes($orderDelay)."', '', '', '', '', '".addslashes($orderLensName)."', '".addslashes($orderNumber)."', '".addslashes($tt_station_id)."', '".addslashes($hoyailog_station_code)."');\n";

		$sqlArray[] = "INSERT INTO my_log (`log_id`,`message`,`message_type`,`log_date`,`customer`) VALUES (NULL,'Imported Order: ".$order['orderNumber']."',1,'".date("Y-m-d H:i:s",time())."','".$store['Account']['Hoya_Account_ID__c']."');";

		$delay = (int)$order["delay"];
		$delayClass = $delay < 0 ? 'delay-late' : ($delay > 0 ? 'delay-warn' : 'delay-ok');
		$delayLabel = $delay === 0 ? '✓' : ($delay > 0 ? '+'.$delay.'d' : $delay.'d');
		echo '<tr>';
		echo '<td>'.htmlspecialchars($order["orderNumber"]).'</td>';
		echo '<td>'.htmlspecialchars($order["orderDate"]).'</td>';
		echo '<td>'.htmlspecialchars($order["deliveryDate"]).'</td>';
		echo '<td class="'.$delayClass.'">'.$delayLabel.'</td>';
		echo '</tr>';
	}
	echo '</table></div>';
	}

}

 $sql = "TRUNCATE TABLE `seiko_daily_data_".$countrycode."`;";
 $result = mysqli_query($conn, $sql);

	  foreach ($sqlArray as $sql){
	       $result = mysqli_query($conn, $sql);
	  }

	  ?>
  </body>
</html>
