<?php

header('Content-Type: text/html; charset=UTF-8');

$baseDir = __DIR__;
$dryrun = isset($_GET['dryrun']) && $_GET['dryrun'] !== '0';
$debug = isset($_GET['debug']) && $_GET['debug'] !== '0';

// Optional private configuration (ignored by git)
@include $baseDir . '/tnt.private.php';

function env_or(string $key, string $default): string {
	$value = getenv($key);
	return ($value === false || $value === '') ? $default : $value;
}

function env_req(string $key): string {
	$value = getenv($key);
	if ($value === false || $value === '') {
		$value = defined($key) ? constant($key) : '';
	}
	if ($value === '') {
		echo "<br/>Missing required environment variable: " . htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		exit;
	}
	return $value;
}

$jwtLocal = $baseDir . '/assets/snippets/jwt/JWT.php';
$configLocal = $baseDir . '/assets/snippets/phpimport/config.php';

if (is_file($jwtLocal)) {
	require_once $jwtLocal;
} else {
	require_once '/home/webpages/lima-city/bipulse-kreativwerkstatt/hoyavisionservice/assets/snippets/jwt/JWT.php';
}

if (!$dryrun) {
	if (is_file($configLocal)) {
		require_once $configLocal;
	} else {
		require_once '/home/webpages/lima-city/bipulse-kreativwerkstatt/hoyavisionservice/assets/snippets/phpimport/config.php';
	}
}

use Firebase\JWT\JWT;


if ($dryrun) {
	$accessToken = 'DRYRUN';
	if ($debug) echo $accessToken;
} else {
	$sfTokenUrl = env_or('SF_TOKEN_URL', 'https://hoya.my.salesforce.com/services/oauth2/token');
	$sfClientId = env_req('SF_CLIENT_ID');
	$sfClientSecret = env_req('SF_CLIENT_SECRET');
	$sfRefreshToken = env_req('SF_REFRESH_TOKEN');

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $sfTokenUrl,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => http_build_query([
			'grant_type' => 'refresh_token',
			'client_id' => $sfClientId,
			'client_secret' => $sfClientSecret,
			'refresh_token' => $sfRefreshToken,
		]),
		CURLOPT_HTTPHEADER => array(
			"Content-Type: application/x-www-form-urlencoded"
		),
	));

	$response = curl_exec($curl);
	curl_close($curl);

	$response_data = json_decode($response, true);
	$accessToken = $response_data['access_token'] ?? '';
	if ($debug) echo $accessToken;

	if ($accessToken === '') {
		echo "<br/>Salesforce token request failed.";
		exit;
	}
}



$payload = [
	"iss" => "salesforce",
	"sub"=> "Salesforce for T&T API access",
	"aud"=> "track-and-trace.hoyailog.com",
	"nbf"=> time(),
	"iat"=> time(),
	"exp"=> time() + 60*5
	];

// Set the secret key for signing the JWT
$secret_key = $dryrun ? 'DRYRUN' : env_req('ILOG_JWT_SECRET');


// $query = urlencode("Select Id,Contact__r.Account.IsDeleted,Contact__r.Account.Name,Contact__r.Account.Id,Contact__r.Account.Hoya_Account_ID__c,Contact__r.Account.Brand__c,Contact__r.Account.Shop_Country__c,Contact__r.Account.Account_Number_ILog__c,Contact__r.Account.Shop_City__c,Contact__r.Account.Shop_Postal_Code__c, Contact__r.Account.Shop_State__c,Contact__r.Account.Shop_Street__c,Contact__r.Language_Pick__c  from Subscription__c Where Active_Subscription__c = true AND Contact__r.Account.Account_Number_ILog__c != null AND Contact__r.Account.Brand__c = 'HOYA' AND  Contact__r.Account.Shop_Country__c = 'NL'");
$query = urlencode("Select Id,Account.IsDeleted,Account.Name,AccountId, Account.Hoya_Account_ID__c,Account.Brand__c,Account.Shop_Country__c,Account.Account_Number_ILog__c,Account.Shop_City__c,Account.Shop_Postal_Code__c, Account.Shop_State__c,Account.Shop_Street__c,Language_Pick__c from Contact Where Active_Subscription__c = true AND Account.Account_Number_ILog__c != null AND Account.Brand__c = 'HOYA' AND Account.Shop_Country__c = 'NL' AND Subscription_Type__c ='Track and Trace' AND RecordType.developerName ='Subscription'");
$url = 'https://hoya.my.salesforce.com/services/data/v50.0/query/?q='.$query;

//$accessToken = "00Db0000000JkWX!AQoAQOErrPpik.y29YyvtSvLsZ0zUcbucoUognGS.N.JLvcZfmhdMXi87EXnRzm1BhlgXXxQxOtVFMrjvcz52wCC1f5bl37v";

if ($dryrun) {
	$stores = [[
		'AccountId' => '001000000000000AAA',
		'Account' => [
			'Account_Number_ILog__c' => '007267',
			'Shop_Country__c' => 'NL',
			'Hoya_Account_ID__c' => 'NL007267',
			'Name' => 'Example Optiek',
			'Shop_Street__c' => 'Hoofdstraat 66',
			'Shop_Postal_Code__c' => '2678 CL',
			'Shop_City__c' => 'De Lier',
		],
	]];
} else {
	$curl = curl_init($url);

	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $accessToken,
			'Content-Type: application/json'
	]);

	$response = curl_exec($curl);

	if (curl_errno($curl)) {
			echo 'Error: ' . curl_error($curl);
			exit;
	}

	$responseData = json_decode($response, true);
	$stores = $responseData['records'] ?? [];

	curl_close($curl);
}


?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Page Title</title>
	  <style>
	  body{
	  	
		  font-family: sans-serif
	  }
	  </style>
  </head>
  <body>
	  
	  <?php

$sqlArray = [];
$customername2 = $customername2 ?? '';
$customercountry = $customercountry ?? '';
$customeremail = $customeremail ?? '';
	  
	  
foreach($stores as $store){

	//print_r($store);
	$ilogId =  $store['Account']['Account_Number_ILog__c'];
	$countrycode = strtolower($store['Account']['Shop_Country__c']);
	$header = [
		"alg" => "HS256",
		"typ"=> "JWT",
		"mfc"=> "HOY",
		"cc"=> "nl",	
		"cn"=> $ilogId
	];


// Encode the JWT with the HS256 algorithm
	$jwt = JWT::encode($payload, $secret_key, 'HS256',null,$header);

// Output the JWT
	if ($dryrun) {
		echo $auth_token = 'DRYRUN_JWT';
		$result = [
			'hydra:member' => [[
				'orderDate' => date('Y-m-d'),
				'deliveryDate' => date('Y-m-d', strtotime('+2 days')),
				'orderNumber' => '123456',
				'stationId' => 10,
				'stationLogistics' => 'ST10',
				'delay' => 0,
				'lensLeft' => 'Example Lens',
				'lensRight' => 'Example Lens',
				'reference1' => 'John Doe',
				'reference2' => '',
			]],
		];
	} else {
		$curl = curl_init();

		$auth_token = $jwt;

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://track-and-trace.hoyailog.com/api/orders?itemsPerPage=50&deliveryDate[after]=".date("Y-m-d"),
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "Authorization: Bearer $auth_token"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		  $result = [];
		} else {
			$result = json_decode($response,true);
		}
	}
	//print_r($result);
	
	
	echo "<br/><b>Client-No.: ".$store['Account']['Hoya_Account_ID__c']."</b><br>" ;
	echo $store['AccountId']."<br>" ;
	echo $store['Account']['Name']."<br>" ;
	echo $store['Account']['Shop_Street__c']."<br>" ;
	echo $store['Account']['Shop_Postal_Code__c']." ".$store['Account']['Shop_City__c']."<br><br>" ;
	foreach(($result['hydra:member'] ?? []) as $order){
		
	$orderDate = $order["orderDate"];
	$deliveryDate = $order["deliveryDate"];
	$orderNumber = 	$order["orderNumber"];
	$tt_station_id =$order["stationId"];
	$hoyailog_station_code = $order["stationLogistics"];
	$orderDelay =  ($order["delay"] > 0) ? "Y" : "N";
	$orderLensName = ($order['lensLeft'] = $order['lensRight']) ? $order['lensLeft'] : $order['lensLeft']." / ".$order['lensRight'];
	$ordconf = ($order['stationId'] != 14 || $order['stationId'] != 15) ? "Y" : "N";
	
		
	$sqlArray[] = "INSERT INTO hoya_daily_data_".$countrycode." (`customernumber`,`sfid`, `customername1`, `customername2`, `customeradress`, `customerzip`, `customercity`, `customercountry`, `l`, `customeremail`, `branchoffice`, `customergroup`, `ordconf`, `send_fax_em`, `orderdate`, `orderpatient`, `orderreference`, `orderplanneddate`, `orderupdateddate`, `orderstatut`, `orderlenstype`, `ordercoating`, `orderlab`, `orderlensname`, `ordernr`,`tt_station_id`,`hoyailog_station_code`) VALUES ('".addslashes($store['Account']['Hoya_Account_ID__c'])."','".addslashes($store['AccountId'])."', '".addslashes($store['Account']['Name'])."', '".addslashes($customername2)."', '".addslashes($store['Account']['Shop_Street__c'])."', '".addslashes($store['Account']['Shop_Postal_Code__c'])."', '".addslashes($store['Account']['Shop_City__c'])."', '".addslashes($customercountry)."', 'nl-nl', '".addslashes($customeremail)."', '', '', '".addslashes($ordconf)."', '', '".addslashes($orderDate)."', '".addslashes($order["reference1"])."', '".addslashes($order["reference2"])."', '".addslashes($deliveryDate)."', '".addslashes($orderDelay)."', '', '', '', '', '".addslashes($orderLensName)."', '".addslashes($orderNumber)."', '".addslashes($tt_station_id)."', '".addslashes($hoyailog_station_code)."');\n";
	$sqlArray[] = "INSERT INTO my_log (`log_id`,`message`,`message_type`,`log_date`,`customer`) VALUES (NULL,'Imported Order: ".$order['orderNumber']."',1,'".date("Y-m-d H:i:s",time())."','".$store['Account']['Hoya_Account_ID__c']."');";
		//write_mysql_log("Imported Order: ".$order["orderNumber"],'1',NULL, $conn);
		echo $order["orderNumber"]." / ".$order["orderDate"]." / ".$order["deliveryDate"]." (Delay: ".$order["delay"].")"."<br>";
	}
	echo "<br>";

}

/*
	
Example
	
['Contact__r']['Account']


[IsDeleted] => 
[Name] => Sanders Optiek
[Hoya_Account_ID__c] => NL007267
[Brand__c] => HOYA
[Shop_Country__c] => NL
[Account_Number_ILog__c] => 007267
[Shop_City__c] => De Lier
[Shop_Postal_Code__c] => 2678 CL
[Shop_State__c] => 
[Shop_Street__c] => Hoofdstraat 66
	
	*/

//echo $output;
 $sql = "TRUNCATE TABLE `hoya_daily_data_".($countrycode ?? 'nl')."`;";
 //$result = $modx->db->query($sql);
	if (!$dryrun && isset($conn)) {
	  $result = mysqli_query($conn, $sql);
	}
	  
	  foreach (($sqlArray ?? []) as $sql){
		  
		  //echo $sql;
	   //$result = $modx->db->query($sql);
		  if (!$dryrun && isset($conn)) {
		    $result = mysqli_query($conn, $sql);
		  }
	  }
	    
	  ?>
	  </p>
  </body>
</html>
	


