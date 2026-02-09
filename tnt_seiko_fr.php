<?php

require_once '/home/webpages/lima-city/bipulse-kreativwerkstatt/hoyavisionservice/assets/snippets/jwt/JWT.php';
require_once '/home/webpages/lima-city/bipulse-kreativwerkstatt/hoyavisionservice/assets/snippets/phpimport/config.php';

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
		'grant_type' => 'refresh_token',
		'client_id' => getenv('SF_CLIENT_ID') ?: '',
		'client_secret' => getenv('SF_CLIENT_SECRET') ?: '',
		'refresh_token' => getenv('SF_REFRESH_TOKEN') ?: '',
	]),
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/x-www-form-urlencoded"
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$response_data = json_decode($response, true);
echo $accessToken = $response_data['access_token'];



$payload = [
	"iss" => "salesforce",
	"sub"=> "Salesforce for T&T API access",
	"aud"=> "track-and-trace.hoyailog.com",
	"nbf"=> time(),
	"iat"=> time(),
	"exp"=> time() + 60*5
	];

// Set the secret key for signing the JWT
$secret_key = getenv('ILOG_JWT_SECRET') ?: '';

if ($secret_key === '' || (getenv('SF_CLIENT_ID') ?: '') === '' || (getenv('SF_CLIENT_SECRET') ?: '') === '' || (getenv('SF_REFRESH_TOKEN') ?: '') === '') {
	echo 'Missing required environment variables (SF_CLIENT_ID, SF_CLIENT_SECRET, SF_REFRESH_TOKEN, ILOG_JWT_SECRET).';
	exit;
}


// $query = urlencode("Select Id,Contact__r.Account.IsDeleted,Contact__r.Account.Name,Contact__r.Account.Id,Contact__r.Account.Hoya_Account_ID__c,Contact__r.Account.Brand__c,Contact__r.Account.Shop_Country__c,Contact__r.Account.Account_Number_ILog__c,Contact__r.Account.Shop_City__c,Contact__r.Account.Shop_Postal_Code__c, Contact__r.Account.Shop_State__c,Contact__r.Account.Shop_Street__c,Contact__r.Language_Pick__c  from Subscription__c Where Active_Subscription__c = true AND Contact__r.Account.Account_Number_ILog__c != null AND Contact__r.Account.Brand__c = 'HOYA' AND  Contact__r.Account.Shop_Country__c = 'NL'");
$query = urlencode("Select Id,Account.IsDeleted,Account.Name,AccountId, Account.Hoya_Account_ID__c,Account.Brand__c,Account.Shop_Country__c,Account.Account_Number_ILog__c,Account.Shop_City__c,Account.Shop_Postal_Code__c, Account.Shop_State__c,Account.Shop_Street__c,Language_Pick__c from Contact Where Active_Subscription__c = true AND Account.Account_Number_ILog__c != null AND Account.Brand__c = 'SEIKO' AND Account.Shop_Country__c = 'FR' AND Subscription_Type__c ='Track and Trace' AND RecordType.developerName ='Subscription'");
$url = 'https://hoya.my.salesforce.com/services/data/v50.0/query/?q='.$query;

//$accessToken = "00Db0000000JkWX!AQoAQOErrPpik.y29YyvtSvLsZ0zUcbucoUognGS.N.JLvcZfmhdMXi87EXnRzm1BhlgXXxQxOtVFMrjvcz52wCC1f5bl37v";

$curl = curl_init($url);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

//$info = curl_getinfo($curl);

//print_r($info);


 $response = curl_exec($curl);

if (curl_errno($curl)) {
    // There was an error executing the cURL request
    echo 'Error: ' . curl_error($curl);
} else {
    // The request was successful
     $responseData = json_decode($response, true);
	
	$stores = $responseData['records'];
    //print_r($responseData);
}

curl_close($curl);


?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Order overview</title>
	  <style>
	  body{
	  	
		  font-family: sans-serif
	  }
	  </style>
  </head>
  <body>
	  
	  <?php
	  
	  
foreach($stores as $store){

	//print_r($store);
	$ilogId =  ltrim($store['Account']['Account_Number_ILog__c'],"0");
	$countrycode = strtolower($store['Account']['Shop_Country__c']);
	$header = [
		"alg" => "HS256",
		"typ"=> "JWT",
		"mfc"=> "SEI",
		"cc"=> "sei",	
		"cn"=> $ilogId
	];
	//print_r($header);
// Encode the JWT with the HS256 algorithm
	$jwt = JWT::encode($payload, $secret_key, 'HS256',null,$header);

// Output the JWT

	$curl = curl_init();

	$auth_token = $jwt;


curl_setopt_array($curl, array(
  // CURLOPT_URL => "https://track-and-trace.hoyailog.com/api/orders?itemsPerPage=50&deliveryDate[after]=".date("Y-m-d")."&manufacturer.code=sei",
   CURLOPT_URL => "https://track-and-trace.hoyailog.com/api/orders?itemsPerPage=200&deliveryDate[after]=".date("Y-m-d", strtotime("-1 week"))."&manufacturer.code=sei",

  //CURLOPT_URL => "https://track-and-trace.hoyailog.com/api/orders?itemsPerPage=50&deliveryDate=2023-12-01&manufacturer.code=sei",
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
	} else {
	  //echo $response;
		$result = json_decode($response,true);
	
	
	}

	
	//print_r($result);
	
	
	echo "<br/><b>Client-No.: ".$store['Account']['Hoya_Account_ID__c']."</b><br>" ;
	echo "iLog-No.: ".$store['Account']['Account_Number_ILog__c']."<br>";
	echo "AccountId: ".$store['AccountId']."<br><br>" ;
	echo $store['Account']['Name']."<br>" ;
	echo $store['Account']['Shop_Street__c']."<br>" ;
	echo $store['Account']['Shop_Postal_Code__c']." ".$store['Account']['Shop_City__c']."<br><br>" ;

	echo "<ol>";
	foreach($result['hydra:member'] as $order){
		
	$orderDate = $order["orderDate"];
	$deliveryDate = $order["deliveryDate"];
	$orderNumber = 	$order["orderNumber"];
	$tt_station_id =$order["stationId"];
	$hoyailog_station_code = $order["stationLogistics"];
	$orderDelay =  ($order["delay"] > 0) ? "Y" : "N";
	$orderLensName = ($order['lensLeft'] = $order['lensRight']) ? $order['lensLeft'] : $order['lensLeft']." / ".$order['lensRight'];
	$ordconf = ($order['stationId'] != 14 || $order['stationId'] != 15) ? "Y" : "N";
	
		echo "<li>";
		echo $sqlArray[] = "INSERT INTO seiko_daily_data_".$countrycode." (`customernumber`,`sfid`, `customername1`, `customername2`, `customeradress`, `customerzip`, `customercity`, `customercountry`, `l`, `customeremail`, `branchoffice`, `customergroup`, `ordconf`, `send_fax_em`, `orderdate`, `orderpatient`, `orderreference`, `orderplanneddate`, `orderupdateddate`, `orderstatut`, `orderlenstype`, `ordercoating`, `orderlab`, `orderlensname`, `ordernr`,`tt_station_id`,`hoyailog_station_code`) VALUES ('".addslashes($store['Account']['Hoya_Account_ID__c'])."','".addslashes($store['AccountId'])."', '".addslashes($store['Account']['Name'])."', '".addslashes($customername2)."', '".addslashes($store['Account']['Shop_Street__c'])."', '".addslashes($store['Account']['Shop_Postal_Code__c'])."', '".addslashes($store['Account']['Shop_City__c'])."', '".addslashes($customercountry)."', 'fr-fr', '".addslashes($customeremail)."', '', '', '".addslashes($ordconf)."', '', '".addslashes($orderDate)."', '".addslashes($order["reference1"])."', '".addslashes($order["reference2"])."', '".addslashes($deliveryDate)."', '".addslashes($orderDelay)."', '', '', '', '', '".addslashes($orderLensName)."', '".addslashes($orderNumber)."', '".addslashes($tt_station_id)."', '".addslashes($hoyailog_station_code)."');\n";

		$sqlArray[] = "INSERT INTO my_log (`log_id`,`message`,`message_type`,`log_date`,`customer`) VALUES (NULL,'Imported Order: ".$order['orderNumber']."',1,'".date("Y-m-d H:i:s",time())."','".$store['Account']['Hoya_Account_ID__c']."');";
		//write_mysql_log("Imported Order: ".$order["orderNumber"],'1',NULL, $conn);
		echo $order["orderNumber"]." / ".$order["orderDate"]." / ".$order["deliveryDate"]." (Delay: ".$order["delay"].")"."</li><br>";
	}
	echo "</ol>";
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
 $sql = "TRUNCATE TABLE `seiko_daily_data_".$countrycode."`;";
 //$result = $modx->db->query($sql);
 $result = mysqli_query($conn, $sql);
	  
	  foreach ($sqlArray as $sql){
		  
		  //echo $sql;
	   //$result = $modx->db->query($sql);
       $result = mysqli_query($conn, $sql);
	  }
	    
	  ?>
	  </p>
  </body>
</html>
	


