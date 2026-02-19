<?php
/**
 * @author bipulse - kreativwerkstatt
 * @email info@bipulse.de
 * @create date 2020-12-17 20:59:24
 * @modify date 2020-12-17 20:59:24
 * @desc [description]
 */


/* -------------------------------------------------------------------------- */
/*                              Helper functions                              */
/* -------------------------------------------------------------------------- */

/* ---------------------------- Write to logfile ---------------------------- */

function write_mysql_log($message, $customer, $message_type, $conn)
{
  // Check database connection
  if( ($conn instanceof MySQLi) == false) {
    return array('status' => false, message => 'MySQL connection is invalid');
  }
 
  // Check message
  if($message == '') {
    return array('status' => false, message => 'Message is empty');
  }
 
    // Escape values
  $message     = $conn->escape_string($message);
  $message_type= $conn->escape_string($message_type);

 
  // Construct query
 $sql = "INSERT INTO my_log (message , message_type, customer) VALUES('$message',$message_type,$customer)";
 
  // Execute query and save data
  $result = $conn->query($sql);
 
  if($result) {
    return array('status' => true);  
  }
  else {
    return array('status' => false, message => 'Unable to write to the database');
  }
}


// copies files and non-empty directories
function rcopy($src, $dst) {
  if (file_exists($dst)) rrmdir($dst);
  if (is_dir($src)) {
    mkdir($dst);
    $files = scandir($src);
    foreach ($files as $file)
    if ($file != "." && $file != "..") rcopy("$src/$file", "$dst/$file");
  }
  else if (file_exists($src)) copy($src, $dst);
}


function generateAddressBlock($row,$_lang){

  //return $customeraddress = "<p>".utf8_encode($_lang['customernumber']).": ".$row["customernumber"]."<br />".$row["customername1"]."<br />".$row["customeradress"]."<br />".$row["customerzip"]." ".$row["customercity"]."</p>";
  return $customeraddress = "<b>".strtoupper($row["customername1"])."</b>".strtoupper($row["customeradress"])." - ".strtoupper($row["customerzip"])." ".strtoupper($row["customercity"]);
                

}

function generateDataTable($data){
  
  $i1 = 0;
  $i2 = 0;
  $i3 = 0;
  $i4 = 0;
  $i5 = 0;

  while ($row = mysqli_fetch_array($data)) {

    

    /* ------------------------ Block 0 - new orders ------------------------ */


    //if($row["orderupdateddate"] == "Y"  && strtotime($row["orderplanneddate"]) >= time()){
      if(date("Y-m-d", strtotime($row["orderdate"])) >= date("Y-m-d")){


        $block0 .='<tr>';
        $block0 .='<td class="desktop">'.$row["ordernr"].'<br>';
        if($row["orderpatient"]) $block0 .= ($row["orderpatient"]);
        $block0 .='</td>';
        $block0 .='<td class="desktop">'.($row["orderlensname"]).'</td>';
        $block0 .='<td class="mobile" style="display:none">';
        $block0 .=    $row["ordernr"];
        if($row["orderpatient"]) $block0 .= ' - '.($row["orderpatient"]).'<br>';
        $block0 .= ($row["orderlensname"]);
        $block0 .='</td>';
        if($row["orderplanneddate"]) {$block0 .='<td style="text-align:center" align="center">'.date("d.m.Y", strtotime($row["orderplanneddate"])).'</td>';}else{$block0 .='<td style="text-align:center" align="center"> - </td>';};
        $block0 .='<td style="text-align:center" align="center">'.date("d.m.Y", strtotime($row["orderdate"])).'</td>';
        $block0 .='</tr>';




    }



    /* ------------------------ Block 1 - Updated orders ------------------------ */


    //if($row["orderupdateddate"] == "Y"  && strtotime($row["orderplanneddate"]) >= time()){
    if($row["orderupdateddate"] == "Y"  && $row["tt_station_id"] >= 1 && $row["tt_station_id"] <= 13){

      $block1 .='<tr>';
      $block1 .='<td class="desktop">'.$row["ordernr"].'<br>';
      if($row["orderpatient"]) $block1 .= ($row["orderpatient"]);
      $block1 .='</td>';
      $block1 .='<td class="desktop">'.($row["orderlensname"]).'</td>';
      $block1 .='<td class="mobile" style="display:none">';
      $block1 .=    $row["ordernr"];
      if($row["orderpatient"]) $block1 .= ' - '.($row["orderpatient"]).'<br>';
      $block1 .= ($row["orderlensname"]);
      $block1 .='</td>';
      if($row["orderplanneddate"]) {$block1 .='<td style="text-align:center" align="center">'.date("d.m.Y", strtotime($row["orderplanneddate"])).'</td>';}else{$block1 .='<td style="text-align:center" align="center"> - </td>';};
      $block1 .='<td style="text-align:center" align="center">'.date("d.m.Y", strtotime($row["orderdate"])).'</td>';
      $block1 .='</tr>';

    }
    
    /* ----------------- Block 2 - Deliveries in the next 3 days ---------------- */
    
    //if(strtotime($row["orderplanneddate"]) < strtotime(date('Y-m-d', strtotime('+3 days'))) && strtotime($row["orderplanneddate"]) > time()){
      if(strtotime($row["orderplanneddate"]) && strtotime($row["orderplanneddate"]) <=  strtotime('+3 days')  && $row["tt_station_id"] >= 1 && $row["tt_station_id"] <= 13) {

        $block2 .='<tr>';
      $block2 .='<td class="desktop">'.$row["ordernr"].'<br>';
      if($row["orderpatient"]) $block2 .= ($row["orderpatient"]);
      $block2 .='</td>';
      $block2 .='<td class="desktop">'.($row["orderlensname"]).'</td>';
      $block2 .='<td class="mobile" style="display:none">';
      $block2 .=    $row["ordernr"];
      if($row["orderpatient"]) $block2 .= ' - '.($row["orderpatient"]).'<br>';
      $block2 .= ($row["orderlensname"]);
      $block2 .='</td>';
      if($row["orderplanneddate"]) {$block2 .='<td style="text-align:center" align="center">'.date("d.m.Y", strtotime($row["orderplanneddate"])).'</td>';}else{$block2 .='<td style="text-align:center" align="center"> - </td>';};
      $block2 .='<td style="text-align:center" align="center">'.date("d.m.Y", strtotime($row["orderdate"])).'</td>';
      $block2 .='</tr>';
    }
    
    /* ------------------- Block 3 - All confirmed deliveries ------------------- */
    
     //if($row["ordconf"] = "Y" && strtotime($row["orderplanneddate"]) > time()){
      if($row["ordconf"] = "Y"  && $row["tt_station_id"] >= 1 && $row["tt_station_id"] <= 14){
        $block3 .='<tr>';
        $block3 .='<td class="desktop">'.$row["ordernr"].'<br>';
        if($row["orderpatient"]) $block3 .= ($row["orderpatient"]);
        $block3 .='</td>';
        $block3 .='<td class="desktop">'.($row["orderlensname"]).'</td>';
        $block3 .='<td class="mobile" style="display:none">';
        $block3 .=    $row["ordernr"];
        if($row["orderpatient"]) $block3 .= ' - '.($row["orderpatient"]).'<br>';
        $block3 .='</td>';
        if($row["orderplanneddate"]) {$block3 .='<td style="text-align:center" align="center">'.date("d.m.Y", strtotime($row["orderplanneddate"])).'</td>';}else{$block3 .='<td style="text-align:center" align="center"> - </td>';};
        $block3 .='<td style="text-align:center" align="center">'.date("d.m.Y", strtotime($row["orderdate"])).'</td>';
        $block3 .='</tr>';
        }
    


  /* ------------------------ Block 4 - Sended orders ------------------------ */


    //if($row["orderupdateddate"] == "Y"  && strtotime($row["orderplanneddate"]) >= time()){
      if($row["tt_station_id"] == "16" && date("Y-m-d", strtotime($row["orderplanneddate"])) >= date("Y-m-d", strtotime("-1 day"))){

        $block4 .='<tr>';
        $block4 .='<td class="desktop">'.$row["ordernr"].'<br>';
        if($row["orderpatient"]) $block4 .= ($row["orderpatient"]);
        $block4 .='</td>';
        $block4 .='<td class="desktop">'.($row["orderlensname"]).'</td>';
        $block4 .='<td class="mobile" style="display:none">';
        $block4 .=    $row["ordernr"];
        if($row["orderpatient"]) $block4 .= ' - '.($row["orderpatient"]).'<br>';
        $block4 .= strtoupper($row["orderlensname"]);
        $block4 .='</td>';
        if($row["orderplanneddate"]) {$block4 .='<td style="text-align:center" align="center">'.date("d.m.Y", strtotime($row["orderplanneddate"])).'</td>';}else{$block4 .='<td style="text-align:center" align="center"> - </td>';};
        $block4 .='<td style="text-align:center" align="center">'.date("d.m.Y", strtotime($row["orderdate"])).'</td>';
        $block4 .='</tr>';


    }
  }

  $block[0][] = $block1;
  $block[0][] = $block1mobile;
  $block[1][] = $block2;
  $block[1][] = $block2mobile;
  $block[2][] = $block3;
  $block[2][] = $block3mobile;
  $block[3][] = $block0;
  $block[3][] = $block0mobile;
  $block[4][] = $block4;
  $block[4][] = $block4mobile;

    return $block;
}

?>