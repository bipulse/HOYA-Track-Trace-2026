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

  $label = isset($_lang['customernumber']) ? tnt_ensure_utf8((string)$_lang['customernumber']) : 'Customer';
  return $customeraddress = "<p>".$label.": ".$row["customernumber"]."<br />".$row["customername1"]."<br />".$row["customeradress"]."<br />".$row["customerzip"]." ".$row["customercity"]."</p>";

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
      if(date("Y-m-d", strtotime($row["orderdate"])) >= date("Y-m-d", strtotime("-1 day"))){

      



        if($i4%2 == 0)
        {
            $class1 = 'even';
            $bg1 = ' bgcolor="#F0F2F4" style="background-color:#F0F2F4;font-family: sans-serif"';
        }
        else
        {
            $class1 = 'odd';
            $bg1 = ' bgcolor="#E5E7E9" style="background-color:#E5E7E9;font-family: sans-serif"';
        }
        $block0 .= "<tr class='".$class1."'".$bg1."'>";
        $block0 .= "<td align='left'".$bg1.">".$row["ordernr"];
        if($row["orderpatient"]) $block0 .= "<br />".$row["orderpatient"];
        #if($row["orderreference"]) $block0 .= "<br />".$row["orderreference"];
         $block0 .= "</td>";
        $block0 .= "<td  align='left'".$bg1.">".$row["orderlensname"]."</td>";
        $block0 .= "<td align='center'".$bg1.">".date("d/m/Y", strtotime($row["orderplanneddate"]))."</td>";
        # $block0 .= "<td>".$row["orderupdateddate"]."</td>";
         $block0 .= "<td align='center'".$bg1."><strong>".date("d/m/Y", strtotime($row["orderdate"]))."</strong></td>";
        $block0 .= "</tr>";


        $block0mobile .= "<tr class='".$class1.$bg1."'>";
        $block0mobile .= "<td align='left'>".$row["ordernr"];
        if($row["orderpatient"]) $block0mobile .= "<br />".$row["orderpatient"];  
        $block0mobile .= "<br />".$row["orderlensname"];
        $block0mobile .= "</td>";
        $block0mobile .= "<td align='center'>".date("d/m/Y", strtotime($row["orderplanneddate"]))."</td>";
         $block0mobile .= "<td align='center'><strong>".date("d/m/Y", strtotime($row["orderdate"]))."</strong></td>";
        $block0mobile .= "</tr>";

        $i4++;
    }



    /* ------------------------ Block 1 - Updated orders ------------------------ */


    //if($row["orderupdateddate"] == "Y"  && strtotime($row["orderplanneddate"]) >= time()){
    if($row["orderupdateddate"] == "Y"  && $row["tt_station_id"] >= 1 && $row["tt_station_id"] <= 13){

        if($i1%2 == 0)
        {
            $class1 = 'even';
            $bg1 = ' bgcolor="#F0F2F4" style="background-color:#F0F2F4;font-family: sans-serif"';
        }
        else
        {
            $class1 = 'odd';
            $bg1 = ' bgcolor="#E5E7E9" style="background-color:#E5E7E9;font-family: sans-serif"';
        }
        $block1 .= "<tr class='".$class1."'".$bg1."'>";
        $block1 .= "<td align='left'".$bg1.">".$row["ordernr"];
        if($row["orderpatient"]) $block1 .= "<br />".$row["orderpatient"];
        #if($row["orderreference"]) $block1 .= "<br />".$row["orderreference"];
         $block1 .= "</td>";
        $block1 .= "<td  align='left'".$bg1.">".$row["orderlensname"]."</td>";
        $block1 .= "<td align='center'".$bg1.">".date("d/m/Y", strtotime($row["orderplanneddate"]))."</td>";
        # $block1 .= "<td>".$row["orderupdateddate"]."</td>";
         $block1 .= "<td align='center'".$bg1."><strong>".date("d/m/Y", strtotime($row["orderdate"]))."</strong></td>";
        $block1 .= "</tr>";


        $block1mobile .= "<tr class='".$class1.$bg1."'>";
        $block1mobile .= "<td align='left'>".$row["ordernr"];
        if($row["orderpatient"]) $block1mobile .= "<br />".$row["orderpatient"];  
        $block1mobile .= "<br />".$row["orderlensname"];
        $block1mobile .= "</td>";
        $block1mobile .= "<td align='center'>".date("d/m/Y", strtotime($row["orderplanneddate"]))."</td>";
         $block1mobile .= "<td align='center'><strong>".date("d/m/Y", strtotime($row["orderdate"]))."</strong></td>";
        $block1mobile .= "</tr>";

        $i1++;
    }
    
    /* ----------------- Block 2 - Deliveries in the next 3 days ---------------- */
    
    //if(strtotime($row["orderplanneddate"]) < strtotime(date('Y-m-d', strtotime('+3 days'))) && strtotime($row["orderplanneddate"]) > time()){
      if(strtotime($row["orderplanneddate"]) < strtotime(date('Y-m-d', strtotime('+3 days')) && $row["tt_station_id"] >= 1 && $row["tt_station_id"] <= 13) ){

      if($i2%2 == 0)
        {
            $class2 = 'even';
            $bg2 = ' bgcolor="#F0F2F4" style="background-color:#F0F2F4;font-family: sans-serif"';
        }
        else
        {
            $class2 = 'odd';
            $bg2 = ' bgcolor="#E5E7E9" style="background-color:#E5E7E9;font-family: sans-serif"';
        }

      $block2 .= "<tr class='".$class2."'".$bg2."'>";
      $block2 .= "<td align='left'".$bg2.">".$row["ordernr"];
      if($row["orderpatient"]) $block2 .= "<br />".$row["orderpatient"];
     # if($row["orderreference"]) $block2 .= "<br />".$row["orderreference"];
      $block2 .= "</td>";
      $block2 .= "<td align='left'".$bg2.">".$row["orderlensname"]."</td>";
      $block2 .= "<td align='center'".$bg2.">".date("d/m/Y", strtotime($row["orderplanneddate"]))."</td>";
     # $block2 .= "<td>".$row["orderupdateddate"]."</td>";
      $block2 .= "<td align='center'".$bg2."><strong>".date("d/m/Y", strtotime($row["orderdate"]))."</strong></td>";
      $block2 .= "</tr>";

      $block2mobile .= "<tr class='".$class2.$bg2."'>";
      $block2mobile .= "<td align='left'>".$row["ordernr"];
      if($row["orderpatient"]) $block2mobile .= "<br />".$row["orderpatient"];  
      $block2mobile .= "<br />".$row["orderlensname"];
      $block2mobile .= "</td>";
      $block2mobile .= "<td align='center'>".date("d/m/Y", strtotime($row["orderplanneddate"]))."</td>";
       $block2mobile .= "<td align='center'><strong>".date("d/m/Y", strtotime($row["orderdate"]))."</strong></td>";
      $block2mobile .= "</tr>";

      $i2++;
    }
    
    /* ------------------- Block 3 - All confirmed deliveries ------------------- */
    
     //if($row["ordconf"] = "Y" && strtotime($row["orderplanneddate"]) > time()){
      if($row["ordconf"] = "Y"  && $row["tt_station_id"] >= 1 && $row["tt_station_id"] <= 13){
      if($i3%2 == 0)
        {
            $class3 = 'even';
            $bg3 = ' bgcolor="#F0F2F4" style="background-color:#F0F2F4;font-family: sans-serif"';
        }
        else
        {
            $class3 = 'odd';
            $bg3 = ' bgcolor="#E5E7E9" style="background-color:#E5E7E9;font-family: sans-serif"';
        }
      $block3 .= "<tr class='".$class3."'".$bg3."'>";
      $block3 .= "<td align='left'".$bg3.">".$row["ordernr"];
      if($row["orderpatient"]) $block3 .= "<br />".$row["orderpatient"];
      #if($row["orderreference"]) $block3 .= "<br />".$row["orderreference"];
      $block3 .= "</td>";
      $block3 .= "<td  align='left'".$bg3.">".$row["orderlensname"]."</td>";
      $block3 .= "<td align='center'".$bg3.">".date("d/m/Y", strtotime($row["orderplanneddate"]))."</td>";
     # $block3 .= "<td>".$row["orderupdateddate"]."</td>";
      $block3 .= "<td align='center'".$bg3."><strong>".date("d/m/Y", strtotime($row["orderdate"]))."</strong></td>";
      $block3 .= "</tr>";

      $block3mobile .= "<tr class='".$class3.$bg."'>";
      $block3mobile .= "<td align='left'>".$row["ordernr"];
      if($row["orderpatient"]) $block3mobile .= "<br />".$row["orderpatient"];  
      $block3mobile .= "<br />".$row["orderlensname"];
      $block3mobile .= "</td>";
      $block3mobile .= "<td align='center'>".date("d/m/Y", strtotime($row["orderplanneddate"]))."</td>";
       $block3mobile .= "<td align='center'><strong>".date("d/m/Y", strtotime($row["orderdate"]))."</strong></td>";
      $block3mobile .= "</tr>";

      $i3++;
        }
    


  /* ------------------------ Block 4 - Sended orders ------------------------ */


    //if($row["orderupdateddate"] == "Y"  && strtotime($row["orderplanneddate"]) >= time()){
      if($row["tt_station_id"] == "16" && date("Y-m-d", strtotime($row["orderplanneddate"])) >= date("Y-m-d", strtotime("-1 day"))){

        if($i5%2 == 0)
        {
            $class1 = 'even';
            $bg1 = ' bgcolor="#F0F2F4" style="background-color:#F0F2F4;font-family: sans-serif"';
        }
        else
        {
            $class1 = 'odd';
            $bg1 = ' bgcolor="#E5E7E9" style="background-color:#E5E7E9;font-family: sans-serif"';
        }
        $block4 .= "<tr class='".$class1."'".$bg1."'>";
        $block4 .= "<td align='left'".$bg1.">".$row["ordernr"];
        if($row["orderpatient"]) $block4 .= "<br />".$row["orderpatient"];
        #if($row["orderreference"]) $block4 .= "<br />".$row["orderreference"];
         $block4 .= "</td>";
        $block4 .= "<td  align='left'".$bg1.">".$row["orderlensname"]."</td>";
        $block4 .= "<td align='center'".$bg1.">".date("d/m/Y", strtotime($row["orderplanneddate"]))."</td>";
        # $block4 .= "<td>".$row["orderupdateddate"]."</td>";
         $block4 .= "<td align='center'".$bg1."><strong>".date("d/m/Y", strtotime($row["orderdate"]))."</strong></td>";
        $block4 .= "</tr>";


        $block4mobile .= "<tr class='".$class1.$bg1."'>";
        $block4mobile .= "<td align='left'>".$row["ordernr"];
        if($row["orderpatient"]) $block4mobile .= "<br />".$row["orderpatient"];  
        $block4mobile .= "<br />".$row["orderlensname"];
        $block4mobile .= "</td>";
        $block4mobile .= "<td align='center'>".date("d/m/Y", strtotime($row["orderplanneddate"]))."</td>";
         $block4mobile .= "<td align='center'><strong>".date("d/m/Y", strtotime($row["orderdate"]))."</strong></td>";
        $block4mobile .= "</tr>";

        $i5++;
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